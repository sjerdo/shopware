<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Core\Framework\Store\InAppPurchases\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\Authentication\AbstractStoreRequestOptionsProvider;
use Shopware\Core\Framework\Store\InAppPurchase\Services\InAppPurchaseUpdater;
use Shopware\Core\Test\Stub\SystemConfigService\StaticSystemConfigService;

/**
 * @internal
 */
#[Package('checkout')]
#[CoversClass(InAppPurchaseUpdater::class)]
class InAppPurchaseUpdaterTest extends TestCase
{
    public function testUpdateActiveInAppPurchases(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(static::once())
            ->method('request')
            ->with('GET', 'https://test.com', ['query' => ['a'], 'headers' => ['b']])
            ->willReturn(new Response(200, [], '[{"identifier":"TestApp-test","active":true,"expiresAt":"2099-01-01"},{"identifier":"TestApp-test2","active":true,"expiresAt":"2099-01-01"}]'));

        $systemConfig = new StaticSystemConfigService([]);

        $optionsProvider = $this->createMock(AbstractStoreRequestOptionsProvider::class);
        $optionsProvider->expects(static::once())
            ->method('getDefaultQueryParameters')
            ->willReturn(['a']);
        $optionsProvider->expects(static::once())
            ->method('getAuthenticationHeader')
            ->willReturn(['b']);

        $service = new InAppPurchaseUpdater(
            $client,
            $systemConfig,
            'https://test.com',
            $optionsProvider
        );
        $service->updateActiveInAppPurchases(Context::createDefaultContext());

        static::assertSame(json_encode([
            ['identifier' => 'TestApp-test', 'active' => true, 'expiresAt' => '2099-01-01'],
            ['identifier' => 'TestApp-test2', 'active' => true, 'expiresAt' => '2099-01-01'],
        ]), $systemConfig->get('core.store.iapKey'));
    }
}
