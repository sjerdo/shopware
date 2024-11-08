<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Core\Framework\Adapter\Twig\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Adapter\Twig\Extension\InAppPurchaseExtension;
use Shopware\Core\Framework\Test\Store\StaticInAppPurchaseFactory;
use Twig\TwigFunction;

/**
 * @internal
 */
#[CoversClass(InAppPurchaseExtension::class)]
class InAppPurchaseExtensionTest extends TestCase
{
    public function testGetFunctions(): void
    {
        $functions = (new InAppPurchaseExtension(StaticInAppPurchaseFactory::createInAppPurchaseWithFeatures()))->getFunctions();

        static::assertCount(2, $functions);
        static::assertInstanceOf(TwigFunction::class, $functions[0]);
        static::assertInstanceOf(TwigFunction::class, $functions[1]);
        static::assertEquals('inAppPurchase', $functions[0]->getName());
        static::assertEquals('allInAppPurchases', $functions[1]->getName());
    }

    public function testIsActive(): void
    {
        $extension = new InAppPurchaseExtension(StaticInAppPurchaseFactory::createInAppPurchaseWithFeatures(['app' => 'test']));

        static::assertTrue($extension->isActive('app'));
        static::assertFalse($extension->isActive('nonexistent'));
    }

    public function testAll(): void
    {
        $extension = new InAppPurchaseExtension(StaticInAppPurchaseFactory::createInAppPurchaseWithFeatures(['app' => 'test', 'anotherapp' => 'test2']));

        static::assertEquals(['app', 'anotherapp'], $extension->all());
    }
}
