<?php declare(strict_types=1);

namespace LwsEsdSerials\Core\Content\Serials\LwsEsdSerial;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;


class LwsEsdSerialCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return LwsEsdSerialEntity::class;
    }
}
