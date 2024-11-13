<?php declare(strict_types=1);

namespace Shopware\Core\Framework\App\InAppPurchases\Payload;

use Shopware\Core\Framework\App\Payload\Source;
use Shopware\Core\Framework\App\Payload\SourcedPayloadInterface;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\JsonSerializableTrait;

/**
 * @internal
 */
#[Package('checkout')]
class InAppPurchasesPayload implements SourcedPayloadInterface
{
    use JsonSerializableTrait;

    private Source $source;

    /**
     * @param array<int, string> $purchases
     */
    public function __construct(public readonly array $purchases)
    {
    }

    public function setSource(Source $source): void
    {
        $this->source = $source;
    }
}
