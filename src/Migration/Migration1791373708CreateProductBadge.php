<?php declare(strict_types=1);

namespace Swag\ProductBadge\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1791373708CreateProductBadge extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1791373708;
    }

    public function update(Connection $connection): void
    {
         $connection->executeStatement('
             CREATE TABLE IF NOT EXISTS `swag_product_badge` (
                 `id` BINARY(16) NOT NULL,
                 `color` VARCHAR(7) NOT NULL DEFAULT \'#0b539b\',
                 `position` INT NOT NULL DEFAULT 1,
                 `created_at` DATETIME(3) NOT NULL,
                 `updated_at` DATETIME(3) NULL,
                 PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
         ');

         $connection->executeStatement('
             CREATE TABLE IF NOT EXISTS `swag_product_badge_translation` (
                 `swag_product_badge_id` BINARY(16) NOT NULL,
                 `language_id` BINARY(16) NOT NULL,
                 `name` VARCHAR(255) NOT NULL,
                 `created_at` DATETIME(3) NOT NULL,
                 `updated_at` DATETIME(3) NULL,
                 PRIMARY KEY (`swag_product_badge_id`, `language_id`),
                 CONSTRAINT `fk.swag_product_badge_translation.swag_product_badge_id` FOREIGN KEY (`swag_product_badge_id`)
                     REFERENCES `swag_product_badge` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                 CONSTRAINT `fk.swag_product_badge_translation.language_id` FOREIGN KEY (`language_id`)
                     REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
         ');

         $connection->executeStatement('
             CREATE TABLE IF NOT EXISTS `swag_product_badge_product` (
                 `swag_product_badge_id` BINARY(16) NOT NULL,
                 `product_id` BINARY(16) NOT NULL,
                 `product_version_id` BINARY(16) NOT NULL,
                 PRIMARY KEY (`swag_product_badge_id`, `product_id`, `product_version_id`),
                 CONSTRAINT `fk.swag_product_badge_product.swag_product_badge_id` FOREIGN KEY (`swag_product_badge_id`)
                     REFERENCES `swag_product_badge` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                 CONSTRAINT `fk.swag_product_badge_product.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
                     REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
         ');
    }
}
