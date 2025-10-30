<?php declare(strict_types=1);

namespace LwsEsdSerials\Core\Content\Serials\LwsEsdSerial;

use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\Content\Media\MediaEntity;

/**
 * Plugin: LwsEsdSerials
 * Module: Content
 * Domain: Serials
 * Timestamp: 1689332855
 * Entity Name: LwsEsdSerial
 * Table name: lws_esd_serials
 *
 * Usage in services:  <argument type="service" id="lws_esd_serials.repository" />
 *
 */
class LwsEsdSerialEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $serialNumber;

    /**
     * @var \DateTime
     */
    protected $assignDate;

    protected $orderItemId;

    protected ?OrderLineItemEntity $orderItem;

    protected $productId;

    protected ProductEntity $product;

    protected $orderId;

    protected ?OrderEntity $order;


    /**
     * @return string
     */
    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    /**
     * @param string $serialNumber
     */
    public function setSerialNumber(string $serialNumber): void
    {
        $this->serialNumber = $serialNumber;
    }

    /**
     * @return mixed
     */
    public function getOrderItemId()
    {
        return $this->orderItemId;
    }

    /**
     * @param mixed $orderItemId
     */
    public function setOrderItemId($orderItemId): void
    {
        $this->orderItemId = $orderItemId;
    }

    /**
     * @return OrderLineItemEntity
     */
    public function getOrderItem(): ?OrderLineItemEntity
    {
        return $this->orderItem;
    }

    /**
     * @param OrderLineItemEntity $orderItem
     */
    public function setOrderItem(?OrderLineItemEntity $orderItem): void
    {
        $this->orderItem = $orderItem;
    }

    /**
     * @return \DateTime
     */
    public function getAssignDate(): \DateTime
    {
        return $this->assignDate;
    }

    /**
     * @param \DateTime $assignDate
     */
    public function setAssignDate(\DateTime $assignDate): void
    {
        $this->assignDate = $assignDate;
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param mixed $productId
     */
    public function setProductId($productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return ProductEntity
     */
    public function getProduct(): ProductEntity
    {
        return $this->product;
    }

    /**
     * @param ProductEntity $product
     */
    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param mixed $orderId
     */
    public function setOrderId($orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return OrderEntity|null
     */
    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    /**
     * @param OrderEntity|null $order
     */
    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
    }
}
