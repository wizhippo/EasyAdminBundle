<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Config;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use PHPUnit\Framework\TestCase;

class ActionTest extends TestCase
{
    public function testStringLabelForStaticLabelGeneration()
    {
        $actionConfig = Action::new(Action::DELETE)
            ->setLabel('Delete Me!')
            ->linkToCrudAction('');

        $this->assertSame('Delete Me!', $actionConfig->getAsDto()->getLabel());
    }

    public function testCallableLabelForDynamicLabelGeneration()
    {
        $callable = static function (object $entity) {
            return sprintf('Delete %s', $entity);
        };

        $actionConfig = Action::new(Action::DELETE)
            ->setLabel($callable)
            ->linkToCrudAction('');

        $dto = $actionConfig->getAsDto();

        $this->assertNull($dto->getLabel());

        $dto->computeLabel($this->getEntityDto('1337'));

        $this->assertSame('Delete #1337', $dto->getLabel());
    }

    public function testDefaultCssClass()
    {
        $actionConfig = Action::new(Action::DELETE)->linkToCrudAction('');

        $this->assertSame('', $actionConfig->getAsDto()->getCssClass());
        $this->assertSame('', $actionConfig->getAsDto()->getAddedCssClass());
    }

    public function testSetCssClass()
    {
        $actionConfig = Action::new(Action::DELETE)->linkToCrudAction('')
            ->setCssClass('foo');

        $this->assertSame('foo', $actionConfig->getAsDto()->getCssClass());
        $this->assertSame('', $actionConfig->getAsDto()->getAddedCssClass());
    }

    public function testAddCssClass()
    {
        $actionConfig = Action::new(Action::DELETE)->linkToCrudAction('')
            ->addCssClass('foo');

        $this->assertSame('', $actionConfig->getAsDto()->getCssClass());
        $this->assertSame('foo', $actionConfig->getAsDto()->getAddedCssClass());
    }

    public function testSetAndAddCssClass()
    {
        $actionConfig = Action::new(Action::DELETE)->linkToCrudAction('')
            ->setCssClass('foo')->addCssClass('bar');

        $this->assertSame('foo', $actionConfig->getAsDto()->getCssClass());
        $this->assertSame('bar', $actionConfig->getAsDto()->getAddedCssClass());
    }

    public function testSetAndAddCssClassWithSpaces()
    {
        $actionConfig = Action::new(Action::DELETE)->linkToCrudAction('')
            ->setCssClass('      foo1   foo2  ')->addCssClass('     bar1    bar2   ');

        $this->assertSame('foo1   foo2', $actionConfig->getAsDto()->getCssClass());
        $this->assertSame('bar1    bar2', $actionConfig->getAsDto()->getAddedCssClass());
    }

    private function getEntityDto(string $entityId): EntityDto
    {
        $entityDtoMock = $this->createMock(EntityDto::class);
        $entityDtoMock
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn(
                new class($entityId) {
                    private $entityId;

                    public function __construct(string $entityId)
                    {
                        $this->entityId = $entityId;
                    }

                    public function __toString(): string
                    {
                        return sprintf('#%s', $this->entityId);
                    }
                }
            );

        return $entityDtoMock;
    }
}
