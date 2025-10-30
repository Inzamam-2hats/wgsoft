<?php

namespace LwsEsdSerials\Core\Content\Serials\Flow;

use LwsEsdSerials\Core\Content\Product\Product\ProductRepo;
use LwsEsdSerials\Core\Content\Serials\LwsEsdSerial\LwsEsdSerialCollection;
use LwsEsdSerials\Core\Content\Serials\LwsEsdSerial\LwsEsdSerialEntity;
use LwsEsdSerials\Core\Content\Serials\LwsEsdSerial\LwsEsdSerialsRepo;
use Monolog\Logger;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItemDownload\OrderLineItemDownloadEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Flow\Dispatching\Action\GrantDownloadAccessAction;
use Shopware\Core\Content\Flow\Dispatching\StorableFlow;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\State;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Event\OrderAware;
use Shopware\Core\Framework\Struct\ArrayStruct;

class GrantDownloadAccessActionWithSerial extends GrantDownloadAccessAction
{

    public function __construct(
        private readonly GrantDownloadAccessAction $grantAccessActionService,
        private readonly LwsEsdSerialsRepo $lwsEsdSerialsRepository,
        private readonly ProductRepo $productRepository,
        private readonly Logger $logger)
    {
    }

    public function handleFlow(StorableFlow $flow): void
    {
        $this->grantAccessActionService->handleFlow($flow);

        if (!$flow->hasData(OrderAware::ORDER)) {
            return;
        }

        /** @var OrderEntity $order */
        $order = $flow->getData(OrderAware::ORDER);

        $config = $flow->getConfig();
        if (!empty($config['value'])) {
            $serialUpdates = $this->assignSerials($flow->getContext(), $config, $order);

            if (!empty($serialUpdates) && is_array($serialUpdates)) {
                $this->lwsEsdSerialsRepository->update($serialUpdates, $flow->getContext());
            }
        } else {
            $serialsToRevoke = $this->revokeSerials($flow->getContext(), $config, $order);
            $this->lwsEsdSerialsRepository->update($serialsToRevoke, $flow->getContext());
        }

    }

    public function assignSerials(Context $context, array $config, OrderEntity $order) {

        if (!isset($config['value'])) {
            return;
        }

        $lineItems = $order->getLineItems();

        if (!$lineItems) {
            return;
        }

        $serialUpdates = array();
        $serials = array();

        foreach ($lineItems->filterGoodsFlat() as $lineItem) {

            $states = $lineItem->getStates();

            if (!$lineItem->getDownloads() || !\in_array(State::IS_DOWNLOAD, $states, true)) {
                continue;
            }

            $productIdForSerials = $this->getProductForSerial($lineItem, $context);
            if (empty($productIdForSerials)) {
                return array();
            }

            $freeSerials = $this->lwsEsdSerialsRepository->getFreeSerials($productIdForSerials, $context);
            $assignedSerials = $this->lwsEsdSerialsRepository->getCountAssignedSerials($lineItem->getId(), $context);
            $quantity = $lineItem->getQuantity();

            $quantity = $quantity - $assignedSerials;
            if ($quantity > 0) {
                if (empty($freeSerials)) {
                    $this->logger->error(sprintf('LwsEsdSerials: No free serials for product %s (product number %s)', $lineItem->getLabel(), $lineItem->getPayload()['productNumber']));
                    return array();
                }

                if (count($freeSerials) < $quantity) {
                    $this->logger->error(sprintf('LwsEsdSerials: Too less free serials for product %s (product number %s)', $lineItem->getLabel(), $lineItem->getPayload()['productNumber']));
                }

                $serials = [];
                /* @var LwsEsdSerialEntity $serial */
                foreach ($freeSerials as $serial) {

                    if (count($serials) > $quantity - 1) {
                        break;
                    }

                    $serialUpdates[] = [
                        'id' => $serial->getId(),
                        'orderId' => $order->getId(),
                        'orderItemId' => $lineItem->getId(),
                        'assignDate' => new \DateTime()
                    ];

                    $serials[] = $serial;
                }

                if (!empty($serials)) {
                    $lineItem->addExtension('lwsEsdSerials', new ArrayStruct($serials));
                }
            }

        }



        return $serialUpdates;
    }

    public function revokeSerials(Context $context, array $config, OrderEntity $order) {

        if (!isset($config['value'])) {
            return;
        }

        $lineItems = $order->getLineItems();

        if (!$lineItems) {
            return;
        }

        $serialsToRevoke = array();

        foreach ($lineItems->filterGoodsFlat() as $lineItem) {

            $states = $lineItem->getStates();

            if (!\in_array(State::IS_DOWNLOAD, $states, true)) {
                continue;
            }

            $assignedSerials = $this->lwsEsdSerialsRepository->getSerialsByOrderItemId($lineItem->getId(), $context);

            if (!empty($assignedSerials)) {
                foreach ($assignedSerials as $serial) {
                    $serialsToRevoke[] = [
                        'id' => $serial->getId(),
                        'orderId' => null,
                        'orderItemId' => null,
                        'assignDate' => null
                    ];
                }

            }
        }

        return $serialsToRevoke;
    }

    private function getProductForSerial($lineItem, $context) {
        $product = $this->productRepository->getProduct($lineItem->getProductId(), $context);

        if (empty($product)) {
            return;
        }

        if ($this->lwsEsdSerialsRepository->hasSerials($lineItem->getProductId(), $context)) {
            return $lineItem->getProductId();
        } else {
            $parentId = $product->getParentId();
            if (!empty($parentId) && $this->lwsEsdSerialsRepository->hasSerials($parentId, $context)) {
                return $parentId;
            } else {
                //the download has no serials
                return;
            }
        }
    }





}
