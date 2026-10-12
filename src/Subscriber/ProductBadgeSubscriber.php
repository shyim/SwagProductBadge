<?php declare(strict_types=1);

namespace Swag\ProductBadge\Subscriber;

use Shopware\Core\Content\Product\Events\ProductListingCollectFilterEvent;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\SalesChannel\Listing\Filter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\EntityAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\ProductBadge\Entity\ProductBadgeEntity;
use Swag\ProductBadge\Extension\ProductExtension;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;

class ProductBadgeSubscriber
{
    final public const FILTER_NAME = 'swag-product-badge';

    #[AsEventListener]
    public function onCriteria(ProductListingCriteriaEvent $event): void
    {
        $event->getCriteria()->getAssociation(ProductExtension::EXTENSION_NAME);
    }

    #[AsEventListener]
    public function onCollectFilter(ProductListingCollectFilterEvent $event): void
    {
        $ids = $this->getBadgeIds($event->getRequest());

        $event->getFilters()->add(new Filter(
            self::FILTER_NAME,
            $ids !== [],
            [new EntityAggregation(self::FILTER_NAME, 'product.' . ProductExtension::EXTENSION_NAME . '.id', ProductBadgeEntity::ENTITY_NAME)],
            new EqualsAnyFilter('product.' . ProductExtension::EXTENSION_NAME . '.id', $ids),
            $ids
        ));
    }

    /**
     * @return list<string>
     */
    private function getBadgeIds(Request $request): array
    {
        $ids = $request->query->get('swag-product-badge');

        if (\is_string($ids)) {
            $ids = explode('|', $ids);
        }

        return array_values(array_filter((array) $ids, static fn (mixed $id) => \is_string($id) && Uuid::isValid($id)));
    }
}
