<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $config = [
        'dbal' => [
            'url' => 'sqlite:///:memory:',
        ],
        'orm' => [
            'auto_mapping' => true,
            'mappings' => [
                'AdminRouteTestApplication' => [
                    'is_bundle' => false,
                    'type' => 'attribute',
                    'dir' => '%kernel.project_dir%/src/Entity',
                    'prefix' => 'EasyCorp\Bundle\EasyAdminBundle\Tests\AdminRouteTestApplication\Entity',
                    'alias' => 'AdminRouteTestApplication',
                ],
            ],
        ],
    ];

    // doctrine-bundle 2.x compatibility
    if (class_exists(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\CacheCompatibilityPass::class)) {
        $config['orm']['auto_generate_proxy_classes'] = true;
    }

    $container->extension('doctrine', $config);
};
