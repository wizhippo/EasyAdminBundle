<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\AdminRouteTestApplication\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test case 3: Controller with partial class-level configuration
 * The class only defines the route path prefix, not the name.
 */
#[AdminRoute(
    routePath: '/reports',
    allowedDashboards: [SecondDashboardController::class]
)]
class ReportsController extends AbstractController
{
    #[AdminRoute(
        routePath: '/sales',
        routeName: 'sales_report'
    )]
    public function salesReport(): Response
    {
        return new Response('Sales Report');
    }

    #[AdminRoute(
        routePath: '/inventory',
        routeName: 'inventory_report'
    )]
    public function inventoryReport(): Response
    {
        return new Response('Inventory Report');
    }
}
