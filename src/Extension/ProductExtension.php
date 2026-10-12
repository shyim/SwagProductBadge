<?php declare(strict_types=1);

namespace Swag\ProductBadge\Extension;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\BulkEntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Swag\ProductBadge\Entity\ProductBadgeEntity;

class ProductExtension extends BulkEntityExtension
{
    final public const EXTENSION_NAME = 'swagProductBadges';

    /**
     * @return \Generator<string, list<ManyToManyAssociationField>>
     */
    public function collect(): \Generator
    {
        yield ProductDefinition::ENTITY_NAME => [
            (new ManyToManyAssociationField(
                self::EXTENSION_NAME,
                ProductBadgeEntity::ENTITY_NAME,
                'swag_product_badge_product',
                'product_id',
                'swag_product_badge_id'
            ))->addFlags(new ApiAware(), new CascadeDelete()),
        ];
    }
}
