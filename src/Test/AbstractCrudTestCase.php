<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Test;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\CrudControllerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\DashboardControllerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Test\Trait\CrudTestActions;
use EasyCorp\Bundle\EasyAdminBundle\Test\Trait\CrudTestIndexAsserts;
use EasyCorp\Bundle\EasyAdminBundle\Test\Trait\CrudTestUrlGeneration;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractCrudTestCase extends WebTestCase
{
    use CrudTestActions;
    use CrudTestIndexAsserts;
    use CrudTestUrlGeneration;

    protected KernelBrowser $client;
    protected AdminUrlGeneratorInterface $adminUrlGenerator;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;

        $adminUrlGenerator = $container->get(AdminUrlGenerator::class);
        \assert($adminUrlGenerator instanceof AdminUrlGenerator);
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    /**
     * @return class-string<CrudControllerInterface> returns the tested Controller Fqcn
     */
    abstract protected function getControllerFqcn(): string;

    /**
     * @return class-string<DashboardControllerInterface> returns the tested Controller Fqcn
     */
    abstract protected function getDashboardFqcn(): string;
}
