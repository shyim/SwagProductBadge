<?php declare(strict_types=1);

namespace Swag\ProductBadge;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class SwagProductBadge extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $connection = $this->container?->get(Connection::class);
        if (!$connection instanceof Connection) {
            throw new \RuntimeException('Database connection is not available during uninstall.');
        }

        $connection->executeStatement('DROP TABLE IF EXISTS `swag_product_badge_product`');
        $connection->executeStatement('DROP TABLE IF EXISTS `swag_product_badge_translation`');
        $connection->executeStatement('DROP TABLE IF EXISTS `swag_product_badge`');
    }
}
