<?php
declare(strict_types=1);

namespace Tmms\ProductCustomerInputs\Core\Twig;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetProductCustomFieldsHelper extends AbstractExtension
{
    public function __construct(
        private readonly SalesChannelRepository $salesChannelProductRepository
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'tmms_get_product_custom_fields',
                [$this, 'getProductCustomFields']
            ),
        ];
    }

    public function getProductCustomFields(
        SalesChannelContext $context = null,
        string $productId,
    ): array {
        if (!$context) {
            return [];
        }

        $productCustomFields = [];
        $productCriteria = new Criteria();

        $productCriteria->addFilter(
            new EqualsFilter('id', (string) $productId)
        );

        $products = $this->salesChannelProductRepository->search($productCriteria, $context);

        foreach ($products->getElements() as $product) {
            $productCustomFields = $product->getCustomFields();
        }

        return $productCustomFields;
    }
}









