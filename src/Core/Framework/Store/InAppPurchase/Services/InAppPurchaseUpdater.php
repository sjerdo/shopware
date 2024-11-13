<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Store\InAppPurchase\Services;

use GuzzleHttp\ClientInterface;
use Shopware\Core\Framework\App\AppCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\Authentication\AbstractStoreRequestOptionsProvider;
use Shopware\Core\Framework\Store\InAppPurchase;
use Shopware\Core\Framework\Store\InAppPurchase\Event\InAppPurchaseChangedEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[Package('checkout')]
class InAppPurchaseUpdater
{
    /**
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly SystemConfigService $systemConfigService,
        private readonly string $fetchEndpoint,
        private readonly AbstractStoreRequestOptionsProvider $storeRequestOptionsProvider,
        private readonly InAppPurchase $inAppPurchase,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityRepository $appRepository,
    ) {
    }

    public function update(Context $context): void
    {
        $activeIaps = $this->fetchFromStore($context);
        $this->systemConfigService->set(InAppPurchaseProvider::CONFIG_STORE_IAP_KEY, $activeIaps);
        $this->inAppPurchase->reset();
        $this->dispatchEvents($context);
    }

    private function fetchFromStore(Context $context): string
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

    private function dispatchEvents(Context $context): void
    {
        $extensionNames = array_unique($this->inAppPurchase->allPurchases());

        foreach ($extensionNames as $extensionName) {
            $purchaseToken = \json_encode($this->inAppPurchase->getByExtension($extensionName), \JSON_THROW_ON_ERROR);

            $criteria = new Criteria();
            $criteria->setLimit(1);
            $criteria->addFilter(new EqualsFilter('name', $extensionName));
            $appId = $this->appRepository->searchIds($criteria, $context)->firstId();

            $event = new InAppPurchaseChangedEvent($extensionName, $purchaseToken, $appId, $context);
            $this->eventDispatcher->dispatch($event);
        }
    }
}
