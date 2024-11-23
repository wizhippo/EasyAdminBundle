<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Twig\Component;

use EasyCorp\Bundle\EasyAdminBundle\Config\Option\IconSet;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\AssetsDto;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Twig\Component\Icon;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

class IconTest extends TestCase
{
    use ExpectDeprecationTrait;

    /**
     * @dataProvider provideGetInternalIconData
     */
    public function testGetInternalIcon(string $iconName, string $appIconSet, string $expectedOutput): void
    {
        $iconComponent = new Icon($this->getAdminContextProviderMock($appIconSet));
        $iconComponent->name = $iconName;
        $iconDto = $iconComponent->getIcon();

        $this->assertSame('internal:user', $iconDto->getName());
        $this->assertStringEndsWith('assets/icons/internal/user.svg', $iconDto->getPath());
        $this->assertStringContainsString('(Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc.', $iconDto->getSvgContents());
    }

    public function provideGetInternalIconData(): iterable
    {
        // internal icons used in EasyAdmin UI; we test it with different icon sets to
        // test that the icon set is ignored for internal icons and the result is always the same
        yield ['internal:user', IconSet::Internal, 'internal:user'];
        yield ['internal:user', IconSet::Custom, 'internal:user'];
        yield ['internal:user', IconSet::FontAwesome, 'internal:user'];
    }

    /**
     * @dataProvider provideGetFontAwesomeIconData
     *
     * @group legacy (needed for tests that use legacy FontAwesome icon names)
     */
    public function testGetFontAwesomeIcon(string $iconName, string $appIconSet, string $expectedOutput): void
    {
        $iconComponent = new Icon($this->getAdminContextProviderMock($appIconSet));
        $iconComponent->name = $iconName;
        $iconDto = $iconComponent->getIcon();

        $lastDirName = basename(\dirname($iconDto->getPath()));
        $svgFileName = pathinfo($iconDto->getPath(), \PATHINFO_FILENAME);
        $expectedIconName = sprintf('%s:%s', $lastDirName, $svgFileName);

        $this->assertSame($expectedIconName, $iconDto->getName());
        $this->assertStringEndsWith($expectedOutput, $iconDto->getPath());
        $this->assertStringContainsString('(Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc.', $iconDto->getSvgContents());
    }

    public function provideGetFontAwesomeIconData(): iterable
    {
        // FontAwesome icons using the modern format
        yield ['fa6-solid:address-card', IconSet::FontAwesome, 'assets/icons/fa6-solid/address-card.svg'];
        yield ['fa6-regular:face-laugh', IconSet::FontAwesome, 'assets/icons/fa6-regular/face-laugh.svg'];
        yield ['fa6-brands:symfony', IconSet::FontAwesome, 'assets/icons/fa6-brands/symfony.svg'];
        // FontAwesome icons using the old format (it's important to test different locations of the icon name in the CSS class list)
        yield ['fa fa-list', IconSet::FontAwesome, 'assets/icons/fa6-solid/list.svg'];
        yield ['fa-solid fa-list', IconSet::FontAwesome, 'assets/icons/fa6-solid/list.svg'];
        yield ['fa-list fa-solid', IconSet::FontAwesome, 'assets/icons/fa6-solid/list.svg'];
        yield ['fa-list fa-solid fa-fw', IconSet::FontAwesome, 'assets/icons/fa6-solid/list.svg'];
        yield ['fa-list fa-fw fa-solid', IconSet::FontAwesome, 'assets/icons/fa6-solid/list.svg'];
        yield ['fa-brands fa-twitter', IconSet::FontAwesome, 'assets/icons/fa6-brands/twitter.svg'];
        yield ['fa-twitter fa-brands', IconSet::FontAwesome, 'assets/icons/fa6-brands/twitter.svg'];
        yield ['fa-twitter fa-brands fa-fw', IconSet::FontAwesome, 'assets/icons/fa6-brands/twitter.svg'];
        yield ['fa-twitter fa-fw fa-brands', IconSet::FontAwesome, 'assets/icons/fa6-brands/twitter.svg'];
        yield ['fa-clock fa-regular', IconSet::FontAwesome, 'assets/icons/fa6-regular/clock.svg'];
        yield ['fa-regular fa-clock', IconSet::FontAwesome, 'assets/icons/fa6-regular/clock.svg'];
        yield ['fa-regular fa-clock fa-fw', IconSet::FontAwesome, 'assets/icons/fa6-regular/clock.svg'];
        yield ['fa-regular fa-fw fa-clock', IconSet::FontAwesome, 'assets/icons/fa6-regular/clock.svg'];
        yield ['fa-address-card', IconSet::FontAwesome, 'assets/icons/fa6-solid/address-card.svg'];
        yield ['fas fa-address-card', IconSet::FontAwesome, 'assets/icons/fa6-solid/address-card.svg'];
        yield ['fa-address-card fas', IconSet::FontAwesome, 'assets/icons/fa6-solid/address-card.svg'];
        yield ['fa-address-card fas fa-fw', IconSet::FontAwesome, 'assets/icons/fa6-solid/address-card.svg'];
        yield ['fa-address-card fa-fw fas', IconSet::FontAwesome, 'assets/icons/fa6-solid/address-card.svg'];
        yield ['fas fa-fw fa-address-card', IconSet::FontAwesome, 'assets/icons/fa6-solid/address-card.svg'];
        yield ['fas fa-address-card fa-fw', IconSet::FontAwesome, 'assets/icons/fa6-solid/address-card.svg'];
        // FontAwesome icons using the old format and legacy icon names
        yield ['fa-file-text-o', IconSet::FontAwesome, 'assets/icons/fa6-regular/file-lines.svg'];
        yield ['fa fa-file-text-o', IconSet::FontAwesome, 'assets/icons/fa6-regular/file-lines.svg'];
        yield ['far fa-file-text-o', IconSet::FontAwesome, 'assets/icons/fa6-regular/file-lines.svg'];
        yield ['fas fa-file-text-o', IconSet::FontAwesome, 'assets/icons/fa6-regular/file-lines.svg'];
        yield ['fa-file-text-o fa', IconSet::FontAwesome, 'assets/icons/fa6-regular/file-lines.svg'];
        yield ['fa-file-text-o fas', IconSet::FontAwesome, 'assets/icons/fa6-regular/file-lines.svg'];
        yield ['fa-file-text-o far', IconSet::FontAwesome, 'assets/icons/fa6-regular/file-lines.svg'];
        yield ['fa-fw fa-file-text-o fa', IconSet::FontAwesome, 'assets/icons/fa6-regular/file-lines.svg'];
        yield ['fa-fw fa-file-text-o fas', IconSet::FontAwesome, 'assets/icons/fa6-regular/file-lines.svg'];
        yield ['fa-fw fa-file-text-o far', IconSet::FontAwesome, 'assets/icons/fa6-regular/file-lines.svg'];
    }

    /**
     * @dataProvider provideGetCustomIconData
     */
    public function testGetCustomIcon(string $iconName): void
    {
        $iconComponent = new Icon($this->getAdminContextProviderMock(IconSet::Custom));
        $iconComponent->name = $iconName;
        $iconDto = $iconComponent->getIcon();

        $this->assertSame($iconName, $iconDto->getName());
        $this->assertNull($iconDto->getPath());
        $this->assertNull($iconDto->getSvgContents());
    }

    public function provideGetCustomIconData(): iterable
    {
        yield ['custom:my-icon'];
        yield ['another-custom-prefix:some-other-icon'];
    }

    public function testUnknownInternalIcon(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/The icon "internal:this-does-not-exist" does not exist\. Check the icon name spelling and make sure that the "this-does-not-exist\.svg" file exists in the "assets\/icons\/internal\/ directory of EasyAdmin"\./');

        $iconComponent = new Icon($this->getAdminContextProviderMock(IconSet::Internal));
        $iconComponent->name = 'internal:this-does-not-exist';
        $iconComponent->getIcon();
    }

    public function testUnknownFontAwesomeIcon(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/The icon "fa6-solid:this-does-not-exist" does not exist\. After processing by EasyAdmin, this icon corresponds to the file "this-does-not-exist\.svg", which could not be found in any of the FontAwesome directories within the "assets\/icons\/" directory of EasyAdmin\./');

        $iconComponent = new Icon($this->getAdminContextProviderMock(IconSet::FontAwesome));
        $iconComponent->name = 'fa6-solid:this-does-not-exist';
        $iconComponent->getIcon();
    }

    /**
     * @group legacy
     *
     * @dataProvider provideDeprecatedFontAwesomeIconData
     */
    public function testDeprecationWhenUsingLegacyFontAwesomeIcon(string $iconName): void
    {
        $this->expectDeprecation('Since easycorp/easyadmin-bundle 4.15.0: The "file-text-o" icon name was deprecated by FontAwesome. The equivalent icon name that you must use is "fa6-regular:file-lines". Using deprecated icon names will no longer work in EasyAdmin 5.0.0.');

        $iconComponent = new Icon($this->getAdminContextProviderMock(IconSet::FontAwesome));
        $iconComponent->name = $iconName;
        $iconComponent->getIcon();
    }

    public function provideDeprecatedFontAwesomeIconData(): iterable
    {
        yield ['fa-file-text-o'];
        yield ['fa fa-file-text-o'];
        yield ['far fa-file-text-o'];
        yield ['fas fa-file-text-o'];
        yield ['fa-file-text-o fa'];
        yield ['fa-file-text-o fas'];
        yield ['fa-file-text-o far'];
        yield ['fa-fw fa-file-text-o fa'];
        yield ['fa-fw fa-file-text-o fas'];
        yield ['fa-fw fa-file-text-o far'];
    }

    /**
     * @return AdminContextProvider
     */
    private function getAdminContextProviderMock(string $appIconSet)
    {
        $adminContextProvider = $this->getMockBuilder(AdminContextProvider::class)->disableOriginalConstructor()->getMock();
        $adminContext = $this->getMockBuilder(AdminContext::class)->disableOriginalConstructor()->getMock();
        $assetsDto = $this->getMockBuilder(AssetsDto::class)->disableOriginalConstructor()->getMock();
        $assetsDto->method('getIconSet')->willReturn($appIconSet);
        $assetsDto->method('getDefaultIconPrefix')->willReturn(''); // TODO
        $adminContext->method('getAssets')->willReturn($assetsDto);
        $adminContextProvider->method('getContext')->willReturn($adminContext);

        return $adminContextProvider;
    }
}
