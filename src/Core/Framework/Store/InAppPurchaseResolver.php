<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Store;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
final class InAppPurchaseResolver
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function fetchActiveInAppPurchases(): array
    {
        /** @var array<string, string> */
        return $this->connection->fetchAllKeyValue('
            SELECT `identifier`, LOWER(HEX(IFNULL(`app_id`, `plugin_id`))) AS extensionId
            FROM in_app_purchase
            WHERE `active` = 1 AND expires_at > NOW()
        ');
    }
}
