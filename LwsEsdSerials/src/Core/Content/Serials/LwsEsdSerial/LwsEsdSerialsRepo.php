<?php

namespace LwsEsdSerials\Core\Content\Serials\LwsEsdSerial;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class LwsEsdSerialsRepo {

    public function __construct(private readonly EntityRepository $dalRepository) {
    }

    public function getById($id, $context)
    {
        return $this->dalRepository->search(new Criteria([$id]), $context)->first();
    }

    public function update($serialUpdates, $context) {
        $result = $this->dalRepository->update($serialUpdates, $context);
        return $result;
    }

    public function getCountAssignedSerials($orderItemId, $context): int {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('orderItemId', $orderItemId));
        /* @var LwsEsdSerialCollection $entities */
        return $this->dalRepository->search($criteria, $context)->count();
    }

    public function hasSerials($productId, $context) {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));

        /* @var LwsEsdSerialCollection $entities */
        return $this->dalRepository->search($criteria, $context)
                ->count() > 0;
    }
    public function getFreeSerials($productId, $context) {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $criteria->addFilter(new EqualsFilter('orderItemId', null));

        /* @var LwsEsdSerialCollection $entities */
        return $this->dalRepository->search($criteria, $context)->getEntities();
    }

    public function getSerialsByOrderItemId($orderItemId, $context) {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('orderItemId', $orderItemId));

        /* @var LwsEsdSerialCollection $entities */
        return $this->dalRepository->search($criteria, $context)->getEntities();
    }
}
