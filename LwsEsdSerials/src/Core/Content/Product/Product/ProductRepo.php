<?php

namespace LwsEsdSerials\Core\Content\Product\Product;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductRepo
{

    public function __construct(private readonly EntityRepository $dalRepository)
    {
    }

    public function getById($id, $context)
    {
        return $this->dalRepository->search(new Criteria([$id]), $context)->first();
    }

    public function getProduct($productId, $context) {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $productId));

        return $this->dalRepository->search($criteria, $context)->first();
    }

}
