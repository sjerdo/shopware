<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Store\InAppPurchase\Services;

use GuzzleHttp\ClientInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\Authentication\AbstractStoreRequestOptionsProvider;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * @internal
 */
#[Package('checkout')]
class InAppPurchaseUpdater
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly SystemConfigService $systemConfigService,
        private readonly string $fetchEndpoint,
        private readonly AbstractStoreRequestOptionsProvider $storeRequestOptionsProvider
    ) {
    }

    public function updateActiveInAppPurchases(Context $context): void
    {
        $activeIaps = $this->fetchActiveInAppPurchasesFromSBP($context);

        $this->systemConfigService->set(InAppPurchaseProvider::CONFIG_STORE_IAP_KEY, $activeIaps);
    }

    private function fetchActiveInAppPurchasesFromSBP(Context $context): string
    {
        $response = $this->client->request(
            'GET',
            $this->fetchEndpoint,
            [
                'query' => $this->storeRequestOptionsProvider->getDefaultQueryParameters($context),
                'headers' => $this->storeRequestOptionsProvider->getAuthenticationHeader($context),
            ],
        );

        return $response->getBody()->getContents();
    }
}
