<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\AdminRouteTestApplication\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test case 4: Controller with only method-level routes (no class-level attribute).
 */
class StandaloneMethodsController extends AbstractController
{
    #[AdminRoute(
        routePath: '/standalone/action1',
        routeName: 'standalone_action1'
    )]
    public function action1(): Response
    {
        return new Response('Standalone Action 1');
    }

    #[AdminRoute(
        routePath: '/standalone/action2',
        routeName: 'standalone_action2',
        routeOptions: ['methods' => ['POST']]
    )]
    public function action2(): Response
    {
        return new Response('Standalone Action 2');
    }
}
