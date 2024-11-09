<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Core\Framework\Store\InAppPurchases\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Api\Context\ShopApiSource;
use Shopware\Core\Framework\App\AppCollection;
use Shopware\Core\Framework\App\AppEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\InAppPurchase\Api\InAppPurchasesController;
use Shopware\Core\Framework\Store\StoreException;
use Shopware\Core\Framework\Test\Store\StaticInAppPurchaseFactory;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Test\Stub\DataAbstractionLayer\StaticEntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('checkout')]
#[CoversClass(InAppPurchasesController::class)]
class InAppPurchasesControllerTest extends TestCase
{
    private Context $context;

    protected function setUp(): void
    {
        $this->context = Context::createDefaultContext(new AdminApiSource('test-user', 'test-extension'));
    }

    public function testActiveInAppPurchasesWithIncorrectContext(): void
    {
        static::expectException(StoreException::class);
        static::expectExceptionMessage('Expected context source to be "Shopware\Core\Framework\Api\Context\AdminApiSource" but got "Shopware\Core\Framework\Api\Context\ShopApiSource".');

        $this->createController()->activeInAppPurchases(
            Context::createDefaultContext(new ShopApiSource('test-channel'))
        );
    }

    public function testActiveInAppPurchasesWithNoIntegrationId(): void
    {
        static::expectException(StoreException::class);
        static::expectExceptionMessage('No integration available in context source "Shopware\Core\Framework\Api\Context\AdminApiSource"');

        $this->createController()->activeInAppPurchases(
            $this->context = Context::createDefaultContext(new AdminApiSource('test-user'))
        );
    }

    public function testActiveInAppPurchasesWithNoPurchasesShouldReturnEmptyArray(): void
    {
        $response = $this->createController()->activeInAppPurchases($this->context);
        static::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = $response->getContent();
        static::assertIsString($content);
        static::assertEquals(
            ['inAppPurchases' => []],
            json_decode($content, true, 512, \JSON_THROW_ON_ERROR)
        );
    }

    public function testActiveInAppPurchasesWithPurchasesShouldReturnArrayWithApps(): void
    {
        $controller = $this->createController(['purchase1' => 'test-extension', 'purchase2' => 'test-extension']);

        $response = $controller->activeInAppPurchases($this->context);
        static::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = $response->getContent();
        static::assertIsString($content);
        static::assertEquals(
            ['inAppPurchases' => ['purchase1', 'purchase2']],
            json_decode($content, true, 512, \JSON_THROW_ON_ERROR)
        );

        $controller = $this->createController(['purchase1' => 'test-extension', 'purchase2' => 'another-extension']);

        $response = $controller->activeInAppPurchases($this->context);
        static::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = $response->getContent();
        static::assertIsString($content);
        static::assertEquals(
            ['inAppPurchases' => ['purchase1']],
            json_decode($content, true, 512, \JSON_THROW_ON_ERROR)
        );
    }

    public function testCheckInAppPurchaseActiveWithoutRequiredParameterThrowsError(): void
    {
        static::expectException(StoreException::class);
        static::expectExceptionMessage('Parameter "identifier" is missing.');

        $request = new Request();

        $this->createController()->checkInAppPurchaseActive($request);
    }

    public function testCheckInAppPurchaseActiveWithNonPurchasedAppReturnsFalse(): void
    {
        $request = new Request();
        $request->request->set('identifier', 'nonPurchasedApp');

        $response = $this->createController()->checkInAppPurchaseActive($request);
        static::assertIsString($response->getContent());
        static::assertEquals(
            ['isActive' => false],
            json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR)
        );
    }

    public function testCheckInAppPurchaseActiveWithPurchasedAppReturnsTrue(): void
    {
        $request = new Request();
        $request->request->set('identifier', 'purchase1');

        $controller = $this->createController(['purchase1' => 'extension-1', 'purchase2' => 'extension-2']);

        $response = $controller->checkInAppPurchaseActive($request);
        static::assertIsString($response->getContent());
        static::assertEquals(
            ['isActive' => true],
            json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR)
        );

        $request->request->set('identifier', 'purchase2');
        $response = $controller->checkInAppPurchaseActive($request);
        static::assertIsString($response->getContent());
        static::assertEquals(
            ['isActive' => true],
            json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param array<string,string> $purchases
     */
    private function createController(array $purchases = []): InAppPurchasesController
    {
        $app = new AppEntity();
        $app->setId(Uuid::randomHex());
        $app->setName('test-extension');
        /** @var StaticEntityRepository<AppCollection> $repository */
        $repository = new StaticEntityRepository([new AppCollection([$app])]);

        return new InAppPurchasesController(
            StaticInAppPurchaseFactory::createInAppPurchaseWithFeatures($purchases),
            $repository,
        );
    }
}
