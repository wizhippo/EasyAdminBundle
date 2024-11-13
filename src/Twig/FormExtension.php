<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Benjamin Georgeault <git@wedgesama.fr>
 */
class FormExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('ea_form_ealabel', null, ['node_class' => 'Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => ['html']]),
        ];
    }
}
