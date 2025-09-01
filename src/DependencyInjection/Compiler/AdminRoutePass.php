<?php

namespace EasyCorp\Bundle\EasyAdminBundle\DependencyInjection\Compiler;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\DependencyInjection\EasyAdminExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to automatically discover and tag controllers that use the
 * #[AdminRoute] attribute in the controller class or any of its methods.
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class AdminRoutePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            if (null === $definition->getClass() || $definition->isAbstract()) {
                continue;
            }

            $class = $container->getParameterBag()->resolveValue($definition->getClass());

            // skip certain well-known vendor prefixes and certain common class suffixes
            if (str_ends_with($class, 'Interface')
                || str_ends_with($class, 'Test')
                || str_starts_with($class, 'Symfony\\')
                || str_starts_with($class, 'Doctrine\\')
                || str_starts_with($class, 'Twig\\')
                || str_starts_with($class, 'League\\')
                || str_starts_with($class, 'Monolog\\')
                || str_starts_with($class, 'Nelmio\\')
                || str_starts_with($class, 'Nyholm\\')
                || str_starts_with($class, 'Psr\\')
                || str_starts_with($class, 'Zenstruck\\')
            ) {
                continue;
            }

            try {
                // use class_exists with autoload disabled first to check if already loaded
                if (!class_exists($class, false)) {
                    $reflector = new \ReflectionClass($class);
                } else {
                    $reflector = new \ReflectionClass($class);
                }
            } catch (\Throwable $e) {
                continue;
            }

            try {
                // check first for class-level AdminRoute attribute
                $hasClassAttribute = [] !== $reflector->getAttributes(AdminRoute::class);

                // then, check for method-level AdminRoute attributes
                $hasMethodAttribute = false;
                foreach ($reflector->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                    if ([] !== $method->getAttributes(AdminRoute::class)) {
                        $hasMethodAttribute = true;
                        break;
                    }
                }

                // if the class or any of its methods have the AdminRoute attribute, tag it
                if ($hasClassAttribute || $hasMethodAttribute) {
                    $definition->addTag(EasyAdminExtension::TAG_ADMIN_ROUTE_CONTROLLER);
                }
            } catch (\Throwable $e) {
                // skip any class that causes issues
                continue;
            }
        }
    }
}
