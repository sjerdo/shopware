<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Core\Framework\Store\InAppPurchases\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\InAppPurchase;
use Shopware\Core\Framework\Store\InAppPurchase\Services\InAppPurchaseProvider;
use Shopware\Core\Test\Stub\SystemConfigService\StaticSystemConfigService;

/**
 * @internal
 */
#[CoversClass(InAppPurchaseProvider::class)]
#[Package('checkout')]
class InAppPurchaseProviderTest extends TestCase
{
    public function testActivePurchases(): void
    {
        $config = new StaticSystemConfigService(['core.store.iapKey' => $this->formatConfigKey([
            'active-feature-1' => 'extension-1',
            'active-feature-2' => 'extension-1',
            'active-feature-3' => 'extension-2',
        ])]);
        $iap = new InAppPurchase(new InAppPurchaseProvider($config));

        static::assertTrue($iap->isActive('active-feature-1'));
        static::assertTrue($iap->isActive('active-feature-2'));
        static::assertTrue($iap->isActive('active-feature-3'));
        static::assertFalse($iap->isActive('this-one-is-not'));

        static::assertSame(['active-feature-1', 'active-feature-2', 'active-feature-3'], $iap->all());
        static::assertSame(['active-feature-1', 'active-feature-2'], $iap->getByExtension('extension-1'));
        static::assertSame(['active-feature-3'], $iap->getByExtension('extension-2'));
        static::assertSame([], $iap->getByExtension('extension-3'));
    }

    public function testInactivePurchase(): void
    {
        $config = new StaticSystemConfigService(['core.store.iapKey' => $this->formatConfigKey([
            'inactive-feature' => 'extension',
        ], false)]);
        $iap = new InAppPurchase(new InAppPurchaseProvider($config));

        static::assertFalse($iap->isActive('inactive-feature'));
        static::assertSame([], $iap->all());
        static::assertSame([], $iap->getByExtension('extension'));
    }

    public function testExpiredPurchase(): void
    {
        $config = new StaticSystemConfigService(['core.store.iapKey' => $this->formatConfigKey([
            'expired-feature' => 'extension',
        ], true, '2000-01-01')]);
        $iap = new InAppPurchase(new InAppPurchaseProvider($config));

        static::assertFalse($iap->isActive('expired-feature'));
        static::assertSame([], $iap->all());
        static::assertSame([], $iap->getByExtension('extension'));
    }

    public function testEmptySystemConfig(): void
    {
        $iap = new InAppPurchase(new InAppPurchaseProvider(new StaticSystemConfigService()));

        static::assertEmpty($iap->all());
    }

    public function testInvalidSystemConfig(): void
    {
        $iap = new InAppPurchase(new InAppPurchaseProvider(new StaticSystemConfigService(['core.store.iapKey' => 'not a json'])));

        static::assertEmpty($iap->all());
    }

    /**
     * @param array<string, string> $purchases
     */
    private function formatConfigKey(array $purchases, bool $active = true, string $expiresAt = '2099-01-01'): string
    {
        $formattedActivePurchases = [];
        foreach ($purchases as $identifier => $extensionName) {
            $formattedActivePurchases[] = [
                'identifier' => $extensionName . '_' . $identifier,
                'active' => $active,
                'expiresAt' => $expiresAt,
            ];
        }

        return \json_encode($formattedActivePurchases) ?: '';
    }
}
