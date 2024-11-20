<?php

declare(strict_types=1);

namespace EasyCorp\Bundle\EasyAdminBundle\Router;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Router\AdminRouteGeneratorInterface as BaseAdminRouteGeneratorInterface;

/**
 * This class generates all routes for every EasyAdmin dashboard in the application
 * and provides a utility to get the Symfony route name for a given {dashboard, CRUD controller, action} tuple.
 *
 * The generated ROUTES are based on a set of default route names and paths, but
 * that can be overwritten at the dashboard, controller and method/action level
 * using the #[AdminDashboard], #[AdminCrud] and #[AdminCrud] attributes.
 */
interface AdminRouteGeneratorInterface extends BaseAdminRouteGeneratorInterface
{
    public function usesPrettyUrls(): bool;

    public function findRouteName(string $dashboardFqcn, string $crudControllerFqcn, string $actionName): ?string;
}
