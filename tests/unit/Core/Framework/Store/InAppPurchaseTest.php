<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Core\Framework\Store;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\InAppPurchase;
use Shopware\Core\Framework\Test\Store\StaticInAppPurchaseFactory;

/**
 * @internal
 */
#[CoversClass(InAppPurchase::class)]
#[Package('checkout')]
class InAppPurchaseTest extends TestCase
{
    public function testAll(): void
    {
        $iap = StaticInAppPurchaseFactory::createWithFeatures(['purchase1' => 'extension-1', 'purchase2' => 'extension-2']);

        static::assertSame(['purchase1', 'purchase2'], $iap->all());
    }

    public function testAllPurchases(): void
    {
        $iap = StaticInAppPurchaseFactory::createWithFeatures(['purchase1' => 'extension-1', 'purchase2' => 'extension-2']);
        static::assertSame(['purchase1' => 'extension-1', 'purchase2' => 'extension-2'], $iap->allPurchases());
    }

    public function testIsActive(): void
    {
        $iap = StaticInAppPurchaseFactory::createWithFeatures(['activePurchase' => 'extension-1']);

        static::assertTrue($iap->isActive('activePurchase'));
        static::assertFalse($iap->isActive('inactivePurchase'));
    }

    public function testEmpty(): void
    {
        $iap = StaticInAppPurchaseFactory::createWithFeatures();

        static::assertFalse($iap->isActive('inactivePurchase'));
        static::assertEmpty($iap->all());
    }

    public function testRegisterPurchasesOverridesActivePurchases(): void
    {
        $iap = StaticInAppPurchaseFactory::createWithFeatures(['purchase1' => 'extension-1']);

        static::assertTrue($iap->isActive('purchase1'));

        $iap = StaticInAppPurchaseFactory::createWithFeatures(['purchase2' => 'extension-1']);

        static::assertFalse($iap->isActive('purchase1'));
        static::assertTrue($iap->isActive('purchase2'));
    }

    public function testByExtension(): void
    {
        $iap = StaticInAppPurchaseFactory::createWithFeatures(['purchase1' => 'extension-1', 'purchase2' => 'extension-2']);

        static::assertSame(['purchase1'], $iap->getByExtension('extension-1'));
        static::assertSame(['purchase2'], $iap->getByExtension('extension-2'));
        static::assertEmpty($iap->getByExtension('extension-3'));
    }
}
