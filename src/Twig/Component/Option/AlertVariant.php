<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Twig\Component\Option;

enum AlertVariant: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Success = 'success';
    case Danger = 'danger';
    case Warning = 'warning';
    case Info = 'info';
    case Light = 'light';
    case Dark = 'dark';
    case Notice = 'notice'; // commonly used according to https://symfony.com/doc/current/session.html#flash-messages
    case Error = 'error'; // commonly used according to https://symfony.com/doc/current/session.html#flash-messages

    public function asBootstrapCssClass(): string
    {
        return 'alert-'.match ($this) {
            self::Primary, self::Secondary, self::Success, self::Danger, self::Warning, self::Info, self::Light, self::Dark => $this->value,
            self::Notice => 'info',
            self::Error => 'danger',
        };
    }
}
