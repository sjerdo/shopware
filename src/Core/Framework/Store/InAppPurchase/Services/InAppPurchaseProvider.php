<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Store\InAppPurchase\Services;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * @internal
 */
#[Package('checkout')]
final class InAppPurchaseProvider
{
    public const CONFIG_STORE_IAP_KEY = 'core.store.iapKey';

    public function __construct(
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getActive(): array
    {
        $purchases = $this->systemConfigService->getString(self::CONFIG_STORE_IAP_KEY);
        if (!$purchases) {
            return [];
        }

        $purchases = json_decode($purchases, true);
        if (!\is_array($purchases)) {
            return [];
        }

        return iterator_to_array($this->formatAndFilterActive($purchases));
    }

    /**
     * @param array<array{identifier: string, active: bool, expiresAt: string}> $purchases
     *
     * @return iterable<string, string>
     */
    private function formatAndFilterActive(array $purchases): iterable
    {
        foreach ($purchases as $purchase) {
            if (!$purchase['active']) {
                continue;
            }

            if (new \DateTime($purchase['expiresAt']) < new \DateTime()) {
                continue;
            }

            [$extensionName, $purchaseName] = explode('_', $purchase['identifier']);

            yield $purchaseName => $extensionName;
        }
    }
}
