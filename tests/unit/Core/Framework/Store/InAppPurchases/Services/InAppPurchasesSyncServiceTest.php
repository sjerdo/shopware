<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Core\Framework\Store\InAppPurchases\Services;

use Doctrine\DBAL\Connection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\Authentication\AbstractStoreRequestOptionsProvider;
use Shopware\Core\Framework\Store\InAppPurchase\Services\InAppPurchasesSyncService;

/**
 * @internal
 */
#[Package('checkout')]
#[CoversClass(InAppPurchasesSyncService::class)]
class InAppPurchasesSyncServiceTest extends TestCase
{
    public function testUpdateActiveInAppPurchases(): void
    {
        $expectedUpsertResponse = [
            ['identifier' => 'TestApp-test', 'appId' => 'test-app-id', 'pluginId' => null, 'expiresAt' => '2099-01-01', 'active' => true],
            ['identifier' => 'TestApp-test2', 'appId' => 'test-app-id', 'pluginId' => null, 'expiresAt' => '2099-01-01', 'active' => true],
        ];

        $client = $this->createMock(ClientInterface::class);
        $client->expects(static::once())
            ->method('request')
            ->with('GET', 'https://test.com', ['query' => ['a'], 'headers' => ['b']])
            ->willReturn(new Response(200, [], '[{"identifier":"TestApp-test", "active":true, "expiresAt":"2099-01-01"},{"identifier":"TestApp-test2", "active":true, "expiresAt":"2099-01-01"}]'));

        $iapRepository = $this->createMock(EntityRepository::class);
        $iapRepository->expects(static::once())
            ->method('upsert')
            ->with($expectedUpsertResponse);

        $connection = $this->createMock(Connection::class);
        $connection->expects(static::exactly(2))
            ->method('fetchAllKeyValue')
            ->willReturnOnConsecutiveCalls(['TestApp' => 'test-app-id'], []);

        $optionsProvider = $this->createMock(AbstractStoreRequestOptionsProvider::class);
        $optionsProvider->expects(static::once())
            ->method('getDefaultQueryParameters')
            ->willReturn(['a']);
        $optionsProvider->expects(static::once())
            ->method('getAuthenticationHeader')
            ->willReturn(['b']);

        $service = new InAppPurchasesSyncService(
            $client,
            $iapRepository,
            $connection,
            'https://test.com',
            $optionsProvider
        );
        $service->updateActiveInAppPurchases(Context::createDefaultContext());
    }

    public function testDisableExpiredInAppPurchases(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(static::once())
            ->method('executeQuery')
            ->with('UPDATE in_app_purchase SET active = false WHERE expires_at < NOW()');

        $service = new InAppPurchasesSyncService(
            $this->createMock(ClientInterface::class),
            $this->createMock(EntityRepository::class),
            $connection,
            'https://test.com',
            $this->createMock(AbstractStoreRequestOptionsProvider::class)
        );
        $service->disableExpiredInAppPurchases();
    }
}
