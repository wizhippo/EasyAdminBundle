<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Dto;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class IconDto
{
    private ?string $name = null;
    private ?string $path = null;
    private ?string $svgContents = null;

    private function __construct()
    {
    }

    public static function new(?string $name = null, ?string $path = null, ?string $svgContents = null): self
    {
        $dto = new self();
        $dto->name = $name;
        $dto->path = $path;
        $dto->svgContents = $svgContents;

        return $dto;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getSvgContents(): ?string
    {
        return $this->svgContents;
    }
}
