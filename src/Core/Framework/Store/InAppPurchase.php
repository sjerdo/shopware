<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Store;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Log\Package;
use Symfony\Contracts\Service\ResetInterface;

#[Package('checkout')]
final class InAppPurchase implements ResetInterface
{
    /**
     * @var array<string, string>
     */
    private array $activePurchases = [];

    /**
     * @var array<string, list<string>>
     */
    private array $extensionPurchases = [];

    /**
     * @internal
     */
    public function __construct(
        private readonly InAppPurchaseResolver $inAppPurchaseFetcher
    ) {
    }

    /**
     * @return list<string>
     */
    public function all(): array
    {
        $this->ensureRegistration();

        return \array_keys($this->activePurchases);
    }

    /**
     * @return array<string, string>
     */
    public function allPurchases(): array
    {
        $this->ensureRegistration();

        return $this->activePurchases;
    }

    /**
     * @return list<string>
     */
    public function getByExtension(string $extensionName): array
    {
        $this->ensureRegistration();

        return $this->extensionPurchases[$extensionName] ?? [];
    }

    public function reset(): void
    {
        $this->activePurchases = [];
        $this->extensionPurchases = [];
    }

    public function isActive(string $identifier): bool
    {
        $this->ensureRegistration();

        return \in_array($identifier, \array_keys($this->activePurchases), true);
    }

    private function ensureRegistration(): void
    {
        if (\count($this->activePurchases)) {
            return;
        }

        try {
            $inAppPurchases = $this->inAppPurchaseFetcher->fetchActiveInAppPurchases();
            $this->activePurchases = $inAppPurchases;

            // group by extension id, which is the value of the array
            $this->extensionPurchases = [];

            foreach ($inAppPurchases as $identifier => $extensionId) {
                $this->extensionPurchases[$extensionId][] = $identifier;
            }
        } catch (Exception) {
            // we don't have a database connection, so we can't fetch the active in-app purchases
        }
    }
}
