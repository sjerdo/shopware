<?php declare(strict_types=1);

namespace Shopware\Core\Content\Cms\SalesChannel;

use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

#[Package('buyers-experience')]
class CmsRouteResponse extends StoreApiResponse
{
    /**
     * @var CmsPageEntity
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $object;

    public function __construct(CmsPageEntity $object)
    {
        parent::__construct($object);
    }

    public function getCmsPage(): CmsPageEntity
    {
        return $this->object;
    }
}
