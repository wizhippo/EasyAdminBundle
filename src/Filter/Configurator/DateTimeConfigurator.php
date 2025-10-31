<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Filter\Configurator;

use Doctrine\DBAL\Types\Types;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class DateTimeConfigurator implements FilterConfiguratorInterface
{
    public function supports(FilterDto $filterDto, ?FieldDto $fieldDto, EntityDto $entityDto, AdminContext $context): bool
    {
        return DateTimeFilter::class === $filterDto->getFqcn();
    }

    public function configure(FilterDto $filterDto, ?FieldDto $fieldDto, EntityDto $entityDto, AdminContext $context): void
    {
        if (!isset($entityDto->getClassMetadata()->fieldMappings[$filterDto->getProperty()])) {
            return;
        }

        $fieldMapping = $entityDto->getClassMetadata()->getFieldMapping($filterDto->getProperty());

        if (Types::DATE_MUTABLE === $fieldMapping['type']) {
            $filterDto->setFormTypeOptionIfNotSet('value_type', DateType::class);
        }

        if (Types::DATE_IMMUTABLE === $fieldMapping['type']) {
            $filterDto->setFormTypeOptionIfNotSet('value_type', DateType::class);
            $filterDto->setFormTypeOptionIfNotSet('value_type_options.input', 'datetime_immutable');
        }

        if (Types::TIME_MUTABLE === $fieldMapping['type']) {
            $filterDto->setFormTypeOptionIfNotSet('value_type', TimeType::class);
        }

        if (Types::TIME_IMMUTABLE === $fieldMapping['type']) {
            $filterDto->setFormTypeOptionIfNotSet('value_type', TimeType::class);
            $filterDto->setFormTypeOptionIfNotSet('value_type_options.input', 'datetime_immutable');
        }

        if (\in_array($fieldMapping['type'], [Types::DATETIME_IMMUTABLE, Types::DATETIMETZ_IMMUTABLE], true)) {
            $filterDto->setFormTypeOptionIfNotSet('value_type_options.input', 'datetime_immutable');
        }
    }
}
