<?php declare(strict_types=1);

namespace Swag\ProductBadge\Entity;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Attribute\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Attribute\FieldType;
use Shopware\Core\Framework\DataAbstractionLayer\Attribute\ManyToMany;
use Shopware\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Attribute\Translations;
use Shopware\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;

#[Entity('swag_product_badge')]
class ProductBadgeEntity extends EntityStruct
{
    final public const ENTITY_NAME = 'swag_product_badge';

    #[PrimaryKey]
    #[Field(type: FieldType::UUID, api: true)]
    public string $id;

    #[Field(type: FieldType::STRING, translated: true, api: true)]
    public ?string $name = null;

    #[Field(type: FieldType::STRING, api: true, maxLength: 7)]
    public string $color = '#0b539b';

    #[Field(type: FieldType::INT, api: true)]
    public int $position = 1;

    /**
     * @var array<string, mixed>|null
     */
    #[Translations]
    public ?array $translations = null;

    #[ManyToMany(entity: 'product', mapping: 'swag_product_badge_product')]
    public ?ProductCollection $products = null;
}
