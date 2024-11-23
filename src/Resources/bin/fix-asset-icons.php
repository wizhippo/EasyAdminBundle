#!/usr/bin/env php
<?php

use Symfony\Component\Finder\Finder;

$composerDir = __DIR__.'/../../../vendor/';
if (!is_dir($composerDir)) {
    echo 'Run "composer install" before running this script.'.\PHP_EOL;

    return 1;
}

require_once $composerDir.'/autoload.php';

$assetIconsDir = __DIR__.'/../../../assets/icons';
if (!is_dir($assetIconsDir)) {
    echo 'The directory "'.$assetIconsDir.'" does not exist.'.\PHP_EOL;

    return 1;
}

fixFontAwesomeIcons($assetIconsDir);

function fixFontAwesomeIcons(string $assetIconsDir): void
{
    $finder = new Finder();
    $dirs = $finder->directories()->in($assetIconsDir)->depth(0);

    foreach ($dirs as $dir) {
        if (!str_starts_with($dir->getFilename(), 'fa6-')) {
            continue;
        }

        $files = $finder->files()->in($dir->getPathname())->name('*.svg');
        foreach ($files as $file) {
            $contents = file_get_contents($file->getPathname());
            if (!str_contains($contents, 'fill="currentColor"')) {
                $contents = preg_replace('/<svg([^>]*)>/', '<svg$1 fill="currentColor">', $contents);
                file_put_contents($file->getPathname(), $contents);
            }
        }
    }
}
