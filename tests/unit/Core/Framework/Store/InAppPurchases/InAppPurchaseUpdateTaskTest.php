<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Core\Framework\Store\InAppPurchases;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\InAppPurchase\InAppPurchaseUpdateTask;

/**
 * @internal
 */
#[Package('checkout')]
#[CoversClass(InAppPurchaseUpdateTask::class)]
class InAppPurchaseUpdateTaskTest extends TestCase
{
    public static function testGetTaskName(): void
    {
        static::assertSame('in-app-purchase.update', InAppPurchaseUpdateTask::getTaskName());
    }

    public static function testGetDefaultInterval(): void
    {
        static::assertEquals(60 * 60 * 24, InAppPurchaseUpdateTask::getDefaultInterval());
    }
}
