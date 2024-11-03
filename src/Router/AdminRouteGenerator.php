<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Router;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Router\AdminRouteGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * This class generates all routes for every EasyAdmin dashboard in the application
 * and provides a utility to get the Symfony route name for a given {dashboard, CRUD controller, action} tuple.
 *
 * The generated ROUTES are based on a set of default route names and paths, but
 * that can be overwritten at the dashboard, controller and method/action level
 * using the #[AdminDashboard], #[AdminCrud] and #[AdminCrud] attributes.
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class AdminRouteGenerator implements AdminRouteGeneratorInterface
{
    // the order in which routes are defined here is important because routes
    // are added to the application in the same order and e.g. the path of the
    // 'detail' route collides with the 'new' route and must be defined after it
    private const DEFAULT_ROUTES_CONFIG = [
        'index' => [
            'routePath' => '/',
            'routeName' => 'index',
            'methods' => ['GET'],
        ],
        'new' => [
            'routePath' => '/new',
            'routeName' => 'new',
            'methods' => ['GET', 'POST'],
        ],
        'batchDelete' => [
            'routePath' => '/batchDelete',
            'routeName' => 'batchDelete',
            'methods' => ['POST'],
        ],
        'autocomplete' => [
            'routePath' => '/autocomplete',
            'routeName' => 'autocomplete',
            'methods' => ['GET'],
        ],
        'edit' => [
            'routePath' => '/{entityId}/edit',
            'routeName' => 'edit',
            'methods' => ['GET', 'POST', 'PATCH'],
        ],
        'delete' => [
            'routePath' => '/{entityId}/delete',
            'routeName' => 'delete',
            'methods' => ['POST'],
        ],
        'detail' => [
            'routePath' => '/{entityId}',
            'routeName' => 'detail',
            'methods' => ['GET'],
        ],
    ];

    public function __construct(
        private iterable $dashboardControllers,
        private iterable $crudControllers,
    ) {
    }

    public function generateAll(): RouteCollection
    {
        $collection = new RouteCollection();
        $addedRouteNames = [];
        foreach ($this->dashboardControllers as $dashboardController) {
            $dashboardFqcn = $dashboardController::class;
            [$allowedCrudControllers, $deniedCrudControllers] = $this->getAllowedAndDeniedControllers($dashboardFqcn);
            $defaultRoutesConfig = $this->getDefaultRoutesConfig($dashboardFqcn);
            $dashboardRouteConfig = $this->getDashboardsRouteConfig()[$dashboardFqcn];

            foreach ($this->crudControllers as $crudController) {
                $crudControllerFqcn = $crudController::class;

                if (null !== $allowedCrudControllers && !\in_array($crudControllerFqcn, $allowedCrudControllers, true)) {
                    continue;
                }

                if (null !== $deniedCrudControllers && \in_array($crudControllerFqcn, $deniedCrudControllers, true)) {
                    continue;
                }

                $crudControllerRouteConfig = $this->getCrudControllerRouteConfig($crudControllerFqcn);
                $actionsRouteConfig = array_replace_recursive($defaultRoutesConfig, $this->getCustomActionsConfig($crudControllerFqcn));

                foreach (array_keys($actionsRouteConfig) as $actionName) {
                    $actionRouteConfig = $actionsRouteConfig[$actionName];
                    $crudActionPath = sprintf('%s/%s/%s', $dashboardRouteConfig['routePath'], $crudControllerRouteConfig['routePath'], ltrim($actionRouteConfig['routePath'], '/'));
                    $crudActionRouteName = sprintf('%s_%s_%s', $dashboardRouteConfig['routeName'], $crudControllerRouteConfig['routeName'], $actionRouteConfig['routeName']);

                    $defaults = [
                        '_controller' => $crudControllerFqcn.'::'.$actionName,
                    ];
                    $options = [
                        EA::ROUTE_CREATED_BY_EASYADMIN => true,
                        EA::DASHBOARD_CONTROLLER_FQCN => $dashboardFqcn,
                        EA::CRUD_CONTROLLER_FQCN => $crudControllerFqcn,
                        EA::CRUD_ACTION => $actionName,
                    ];

                    $route = new Route($crudActionPath, defaults: $defaults, options: $options, methods: $actionRouteConfig['methods']);

                    if (\in_array($crudActionRouteName, $addedRouteNames, true)) {
                        throw new \RuntimeException(sprintf('When using pretty URLs, all CRUD controllers must have unique PHP class names to generate unique route names. However, your application has at least two controllers with the FQCN "%s", generating the route "%s". Even if both CRUD controllers are in different namespaces, they cannot have the same class name. Rename one of these controllers to resolve the issue.', $crudControllerFqcn, $crudActionRouteName));
                    }

                    $collection->add($crudActionRouteName, $route);
                    $addedRouteNames[] = $crudActionRouteName;
                }
            }
        }

        return $collection;
    }

    public function findRouteName(string $dashboardFqcn, string $crudControllerFqcn, string $actionName): ?string
    {
        $defaultRoutesConfig = $this->getDefaultRoutesConfig($dashboardFqcn);
        $actionsRouteConfig = array_replace_recursive($defaultRoutesConfig, $this->getCustomActionsConfig($crudControllerFqcn));
        if (!isset($actionsRouteConfig[$actionName])) {
            return null;
        }

        $dashboardRouteConfig = $this->getDashboardsRouteConfig()[$dashboardFqcn];
        $crudControllerRouteConfig = $this->getCrudControllerRouteConfig($crudControllerFqcn);
        $actionRouteConfig = $actionsRouteConfig[$actionName];

        return sprintf('%s_%s_%s', $dashboardRouteConfig['routeName'], $crudControllerRouteConfig['routeName'], $actionRouteConfig['routeName']);
    }

    /**
     * @return array{0: class-string[]|null, 1: class-string[]|null}
     */
    private function getAllowedAndDeniedControllers(string $dashboardFqcn): array
    {
        if (null === $attribute = $this->getPhpAttributeInstance($dashboardFqcn, AdminDashboard::class)) {
            return [null, null];
        }

        if (null !== $attribute->allowedControllers && null !== $attribute->deniedControllers) {
            throw new \RuntimeException(sprintf('In the #[AdminDashboard] attribute of the "%s" dashboard controller, you cannot define both "allowedControllers" and "deniedControllers" at the same time because they are the exact opposite. Use only one of them.', $dashboardFqcn));
        }

        return [$attribute->allowedControllers, $attribute->deniedControllers];
    }

    private function getDefaultRoutesConfig(string $dashboardFqcn): array
    {
        if (null === $dashboardAttribute = $this->getPhpAttributeInstance($dashboardFqcn, AdminDashboard::class)) {
            return self::DEFAULT_ROUTES_CONFIG;
        }

        if (null === $customRoutesConfig = $dashboardAttribute->routes) {
            return self::DEFAULT_ROUTES_CONFIG;
        }

        foreach ($customRoutesConfig as $action => $customRouteConfig) {
            if (\count(array_diff(array_keys($customRouteConfig), ['routePath', 'routeName'])) > 0) {
                throw new \RuntimeException(sprintf('In the #[AdminDashboard] attribute of the "%s" dashboard controller, the route configuration for the "%s" action defines some unsupported keys. You can only define these keys: "routePath" and "routeName".', $dashboardFqcn, $action));
            }

            if (isset($customRouteConfig['routeName']) && !preg_match('/^[a-zA-Z0-9_-]+$/', $customRouteConfig['routeName'])) {
                throw new \RuntimeException(sprintf('In the #[AdminDashboard] attribute of the "%s" dashboard controller, the route name "%s" for the "%s" action is not valid. It can only contain letter, numbers, dashes, and underscores.', $dashboardFqcn, $customRouteConfig['routeName'], $action));
            }

            if (isset($customRouteConfig['routePath']) && \in_array($action, ['edit', 'detail', 'delete'], true) && !str_contains($customRouteConfig['routePath'], '{entityId}')) {
                throw new \RuntimeException(sprintf('In the #[AdminDashboard] attribute of the "%s" dashboard controller, the path for the "%s" action must contain the "{entityId}" placeholder.', $action, $dashboardFqcn));
            }
        }

        return array_replace_recursive(self::DEFAULT_ROUTES_CONFIG, $customRoutesConfig);
    }

    private function getDashboardsRouteConfig(): array
    {
        $config = [];

        foreach ($this->dashboardControllers as $dashboardController) {
            $reflectionClass = new \ReflectionClass($dashboardController);
            $indexMethod = $reflectionClass->getMethod('index');
            $routeAttributeFqcn = class_exists(\Symfony\Component\Routing\Attribute\Route::class) ? \Symfony\Component\Routing\Attribute\Route::class : \Symfony\Component\Routing\Annotation\Route::class;
            $attributes = $indexMethod->getAttributes($routeAttributeFqcn);

            if ([] === $attributes) {
                throw new \RuntimeException(sprintf('When using pretty URLs, the "%s" EasyAdmin dashboard controller must define its route configuration (route name and path) using Symfony\'s #[Route] attribute applied to its "index()" method.', $reflectionClass->getName()));
            }

            if (\count($attributes) > 1) {
                throw new \RuntimeException(sprintf('When using pretty URLs, the "%s" EasyAdmin dashboard controller must define only one #[Route] attribute applied on its "index()" method.', $reflectionClass->getName()));
            }

            $routeAttribute = $attributes[0]->newInstance();
            $config[$reflectionClass->getName()] = [
                'routeName' => $routeAttribute->getName(),
                'routePath' => rtrim($routeAttribute->getPath(), '/'),
            ];
        }

        return $config;
    }

    private function getCrudControllerRouteConfig(string $crudControllerFqcn): array
    {
        $crudControllerConfig = [];

        $reflectionClass = new \ReflectionClass($crudControllerFqcn);
        $attributes = $reflectionClass->getAttributes(AdminCrud::class);
        $attribute = $attributes[0] ?? null;

        // first, check if the CRUD controller defines a custom route config in the #[AdminCrud] attribute
        if (null !== $attribute) {
            if (\count(array_diff(array_keys($attribute->getArguments()), ['routePath', 'routeName'])) > 0) {
                throw new \RuntimeException(sprintf('In the #[AdminCrud] attribute of the "%s" CRUD controller, the route configuration defines some unsupported keys. You can only define these keys: "routePath" and "routeName".', $crudControllerFqcn));
            }

            if (\array_key_exists('routePath', $attribute->getArguments())) {
                $crudControllerConfig['routePath'] = trim($attribute->getArguments()['routePath'], '/');
            }

            if (\array_key_exists('routeName', $attribute->getArguments())) {
                if (!preg_match('/^[a-zA-Z0-9_-]+$/', $attribute->getArguments()['routeName'])) {
                    throw new \RuntimeException(sprintf('In the #[AdminCrud] attribute of the "%s" CRUD controller, the route name "%s" is not valid. It can only contain letter, numbers, dashes, and underscores.', $crudControllerFqcn, $attribute->getArguments()['routeName']));
                }

                $crudControllerConfig['routeName'] = trim($attribute->getArguments()['routeName'], '_');
            }
        }

        // if the CRUD controller doesn't define any or all of the route configuration,
        // use the default values based on the controller's class name
        if (!\array_key_exists('routePath', $crudControllerConfig)) {
            $crudControllerConfig['routePath'] = trim($this->transformCrudControllerNameToSnakeCase($crudControllerFqcn), '/');
        }
        if (!\array_key_exists('routeName', $crudControllerConfig)) {
            $crudControllerConfig['routeName'] = trim($this->transformCrudControllerNameToSnakeCase($crudControllerFqcn), '_');
        }

        return $crudControllerConfig;
    }

    private function getCustomActionsConfig(string $crudControllerFqcn): array
    {
        $customActionsConfig = [];
        $reflectionClass = new \ReflectionClass($crudControllerFqcn);
        $methods = $reflectionClass->getMethods();

        foreach ($methods as $method) {
            $attributes = $method->getAttributes(AdminAction::class);
            if ([] === $attributes) {
                continue;
            }

            $attribute = $attributes[0];
            /** @var AdminAction $attributeInstance */
            $attributeInstance = $attribute->newInstance();
            $action = $method->getName();

            if (\count(array_diff(array_keys($attribute->getArguments()), ['routePath', 'routeName', 'methods'])) > 0) {
                throw new \RuntimeException(sprintf('In the "%s" CRUD controller, the #[AdminAction] attribute applied to the "%s()" action includes some unsupported keys. You can only define these keys: "routePath", "routeName", and "methods".', $crudControllerFqcn, $action));
            }

            if (null !== $attributeInstance->routePath) {
                if (\in_array($action, ['edit', 'detail', 'delete'], true) && !str_contains($attributeInstance->routePath, '{entityId}')) {
                    throw new \RuntimeException(sprintf('In the "%s" CRUD controller, the #[AdminAction] attribute applied to the "%s()" action is missing the "{entityId}" placeholder in its route path.', $crudControllerFqcn, $action));
                }

                $customActionsConfig[$action]['routePath'] = trim($attributeInstance->routePath, '/');
            }

            if (null !== $attributeInstance->routeName) {
                if (!preg_match('/^[a-zA-Z0-9_-]+$/', $attributeInstance->routeName)) {
                    throw new \RuntimeException(sprintf('In the "%s" CRUD controller, the #[AdminAction] attribute applied to the "%s()" action defines an invalid route name: "%s". Valid route names can only contain letters, numbers, dashes, and underscores.', $crudControllerFqcn, $action, $attributeInstance->routeName));
                }

                $customActionsConfig[$action]['routeName'] = trim($attributeInstance->routeName, '_');
            }

            if (\array_key_exists('methods', $attribute->getArguments()) && null !== $attribute->getArguments()['methods'] && \in_array($action, ['index', 'new', 'edit', 'detail', 'delete'], true)) {
                throw new \RuntimeException(sprintf('In the "%s" CRUD controller, the #[AdminAction] attribute applied to the "%s()" action cannot define the "methods" argument because these are built-in EasyAdmin actions and have fixed HTTP methods.', $crudControllerFqcn, $action));
            }

            if (null !== $attributeInstance->methods) {
                $allowedMethods = ['GET', 'POST', 'PATCH', 'PUT'];
                foreach ($attributeInstance->methods as $httpMethod) {
                    if (!\in_array(strtoupper($httpMethod), $allowedMethods, true)) {
                        throw new \RuntimeException(sprintf('In the "%s" CRUD controller, the #[AdminAction] attribute applied to the "%s()" action includes "%s" as part of its HTTP methods. However, the only allowed HTTP methods are: %s', $crudControllerFqcn, $action, $httpMethod, implode(', ', $allowedMethods)));
                    }
                }

                $customActionsConfig[$action]['methods'] = $attributeInstance->methods;
            }
        }

        return $customActionsConfig;
    }

    private function getPhpAttributeInstance(string $classFqcn, string $attributeFqcn): ?object
    {
        $reflectionClass = new \ReflectionClass($classFqcn);
        if ([] === $attributes = $reflectionClass->getAttributes($attributeFqcn)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    // transforms 'App\Controller\Admin\FooBarBazCrudController' into 'foo_bar_baz'
    private function transformCrudControllerNameToSnakeCase(string $crudControllerFqcn): string
    {
        $shortName = str_replace(['CrudController', 'Controller'], '', (new \ReflectionClass($crudControllerFqcn))->getShortName());
        $shortName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName));

        return $shortName;
    }
}
