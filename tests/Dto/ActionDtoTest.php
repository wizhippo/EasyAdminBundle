<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Dto;

use EasyCorp\Bundle\EasyAdminBundle\Dto\ActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use PHPUnit\Framework\TestCase;

final class ActionDtoTest extends TestCase
{
    public function testComputeLabelFromStaticLabel()
    {
        $actionDto = new ActionDto();
        $actionDto->setLabel('Edit');

        $actionDto->computeLabel($this->getEntityDto('42'));

        $this->assertSame('Edit', $actionDto->getLabel());
    }

    public function testComputeLabelFromDynamicLabelCallable()
    {
        $actionDto = new ActionDto();
        $actionDto->setLabel(static function (object $entity) {
            return sprintf('Edit %s', $entity);
        });

        $actionDto->computeLabel($this->getEntityDto('1337'));

        $this->assertSame('Edit #1337', $actionDto->getLabel());
    }

    public function testComputeLabelFailsWithInvalidCallableReturnValueType()
    {
        $actionDto = new ActionDto();
        $actionDto->setLabel(static function (object $entity) {
            return 12345;
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Action label callable must return a string or a Symfony\Contracts\Translation\TranslatableInterface instance but it returned a(n) "integer" value instead.');

        $actionDto->computeLabel($this->getEntityDto('1337'));
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
