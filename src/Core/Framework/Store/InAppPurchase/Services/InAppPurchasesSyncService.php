<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Store\InAppPurchase\Services;

use Doctrine\DBAL\Connection;
use GuzzleHttp\ClientInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\Authentication\AbstractStoreRequestOptionsProvider;
use Shopware\Core\Framework\Store\InAppPurchase\InAppPurchaseCollection;

/**
 * @internal
 */
#[Package('checkout')]
class InAppPurchasesSyncService
{
    /**
     * @param EntityRepository<InAppPurchaseCollection> $iapRepository
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly EntityRepository $iapRepository,
        private readonly Connection $connection,
        private readonly string $fetchEndpoint,
        private readonly AbstractStoreRequestOptionsProvider $storeRequestOptionsProvider
    ) {
    }

    public function updateActiveInAppPurchases(Context $context): void
    {
        $existingApps = $this->fetchExistingExtensionsByType('app');
        $existingPlugins = $this->fetchExistingExtensionsByType('plugin');

        $activeIaps = $this->fetchActiveInAppPurchasesFromSBP($context);

        $iapData = array_map(static function ($iap) use ($existingApps, $existingPlugins) {
            $identifier = strtok($iap['identifier'], '-') ?: '';

            return [
                'identifier' => $iap['identifier'],
                'expiresAt' => $iap['expiresAt'],
                'appId' => $existingApps[$identifier] ?? null,
                'pluginId' => $existingPlugins[$identifier] ?? null,
                'active' => true,
            ];
        }, $activeIaps);

        $this->iapRepository->upsert($iapData, $context);
    }

    public function disableExpiredInAppPurchases(): void
    {
        $this->connection->executeQuery('UPDATE in_app_purchase SET active = false WHERE expires_at < NOW()');
    }

    /**
     * @return array<array-key, mixed>
     */
    private function fetchExistingExtensionsByType(string $type): array
    {
        return $this->connection->fetchAllKeyValue(\sprintf('
            SELECT `name`, LOWER(HEX(`id`)) AS `id`
            FROM `%s`
            WHERE `active` = 1', $type));
    }

    /**
     * @return array<int, array{identifier: string, expiresAt: string|null}>
     */
    private function fetchActiveInAppPurchasesFromSBP(Context $context): array
    {
        $response = $this->client->request(
            'GET',
            $this->fetchEndpoint,
            [
                'query' => $this->storeRequestOptionsProvider->getDefaultQueryParameters($context),
                'headers' => $this->storeRequestOptionsProvider->getAuthenticationHeader($context),
            ],
        );

        return json_decode($response->getBody()->getContents(), true);
    }
}
