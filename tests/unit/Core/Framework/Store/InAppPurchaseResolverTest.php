<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Core\Framework\Store;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\InAppPurchase;
use Shopware\Core\Framework\Store\InAppPurchaseResolver;

/**
 * @internal
 */
#[CoversClass(InAppPurchaseResolver::class)]
#[Package('checkout')]
class InAppPurchaseResolverTest extends TestCase
{
    public function testCompilerPass(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(static::once())
            ->method('fetchAllKeyValue')
            ->willReturn([
                'active-feature-1' => 'extension-1',
                'active-feature-2' => 'extension-1',
                'active-feature-3' => 'extension-2',
            ]);

        $iap = new InAppPurchase(new InAppPurchaseResolver($connection));

        static::assertTrue($iap->isActive('active-feature-1'));
        static::assertTrue($iap->isActive('active-feature-2'));
        static::assertTrue($iap->isActive('active-feature-3'));
        static::assertFalse($iap->isActive('this-one-is-not'));

        static::assertSame(['active-feature-1', 'active-feature-2', 'active-feature-3'], $iap->all());
        static::assertSame(['active-feature-1', 'active-feature-2'], $iap->getByExtension('extension-1'));
        static::assertSame(['active-feature-3'], $iap->getByExtension('extension-2'));
        static::assertSame([], $iap->getByExtension('extension-3'));
    }

    public function testConnectionError(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(static::once())
            ->method('fetchAllKeyValue')
            ->willThrowException(new ConnectionException());

        $iap = new InAppPurchase(new InAppPurchaseResolver($connection));

        static::assertEmpty($iap->all());
    }
}
