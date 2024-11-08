<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Test\Store;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\InAppPurchase;
use Shopware\Core\Framework\Store\InAppPurchaseResolver;
use Shopware\Core\Test\Stub\Doctrine\FakeConnection;

/**
 * @internal
 */
#[Package('checkout')]
class StaticInAppPurchaseFactory
{
    /**
     * @param array<string,string> $activePurchases ['featureIdentifier' => 'extensionId']
     */
    public static function createInAppPurchaseWithFeatures(array $activePurchases = []): InAppPurchase
    {
        $inAppPurchase = new InAppPurchase(
            new InAppPurchaseResolver(new FakeConnection([]))
        );

        // group by extension id, which is the value of the array
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
