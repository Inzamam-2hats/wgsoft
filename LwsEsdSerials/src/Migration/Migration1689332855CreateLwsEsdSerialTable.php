<?php declare(strict_types=1);

namespace LwsEsdSerials\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ListField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1689332855CreateLwsEsdSerialTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1689332855;
    }

    public function update(Connection $connection): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `lws_esd_serials` (
            `id` BINARY(16) NOT NULL,
            `serial_number` VARCHAR(50) NOT NULL,
            `order_item_id` BINARY(16) NULL,
            `order_id` BINARY(16) NULL,
            `product_id` BINARY(16) NULL,
            `assign_date` DATETIME(3) NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3),
          PRIMARY KEY(`id`),
          CONSTRAINT `fk.lws_esd_serials.product` FOREIGN KEY (`product_id`)
          REFERENCES `product` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $connection->executeQuery($sql);

    }


    public function updateDestructive(Connection $connection): void
    {
    }

}
