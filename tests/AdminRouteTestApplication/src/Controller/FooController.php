<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\AdminRouteTestApplication\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test case 2: Controller with class-level prefix and method-level routes.
 */
#[AdminRoute(
    routePath: '/foo',
    routeName: 'foo',
    allowedDashboards: [DashboardController::class]
)]
class FooController extends AbstractController
{
    #[AdminRoute(
        routePath: '/list',
        routeName: 'list'
    )]
    public function list(): Response
    {
        return new Response('Foo List');
    }

    #[AdminRoute(
        routePath: '/export/csv',
        routeName: 'export_csv',
        routeOptions: ['methods' => ['GET', 'POST']]
    )]
    public function exportCsv(): Response
    {
        return new Response('Export CSV');
    }

    /**
     * This method overrides the class-level dashboard restrictions.
     */
    #[AdminRoute(
        routePath: '/public-export',
        routeName: 'public_export',
        allowedDashboards: null,
    )]
    public function publicExport(): Response
    {
        return new Response('Public Export');
    }
}
