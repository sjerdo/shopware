<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Test\Store;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\InAppPurchase;
use Shopware\Core\Framework\Store\InAppPurchase\Services\InAppPurchaseProvider;
use Shopware\Core\Test\Stub\SystemConfigService\StaticSystemConfigService;

/**
 * @internal
 */
#[Package('checkout')]
class StaticInAppPurchaseFactory
{
    /**
     * @param array<string,string> $activePurchases ['featureIdentifier' => 'extensionName']
     */
    public static function createWithFeatures(array $activePurchases = []): InAppPurchase
    {
        $inAppPurchase = new InAppPurchase(new InAppPurchaseProvider(new StaticSystemConfigService()));

        // group by extension name, which is the value of the array
        $extensionPurchases = [];
        foreach ($activePurchases as $identifier => $extensionId) {
            $extensionPurchases[$extensionId][] = $identifier;
        }

        $reflection = new \ReflectionProperty(InAppPurchase::class, 'activePurchases');
        $reflection->setValue($inAppPurchase, $activePurchases);

        $reflection = new \ReflectionProperty(InAppPurchase::class, 'extensionPurchases');
        $reflection->setValue($inAppPurchase, $extensionPurchases);

        return $inAppPurchase;
    }
}
