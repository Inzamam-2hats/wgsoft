<?php


namespace LwsEsdSerials\Core\Checkout\Order\Subscriber;


use LwsEsdSerials\Core\Content\Serials\LwsEsdSerial\LwsEsdSerialsRepo;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Content\Product\State;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderLoadedSubscriber  implements EventSubscriberInterface
{
    public function __construct(private readonly LwsEsdSerialsRepo $serialsRepo)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            OrderEvents::ORDER_LINE_ITEM_LOADED_EVENT => 'addSerialnumbers'
        ];
    }

    public function addSerialnumbers(EntityLoadedEvent $event){

        if (is_array($event->getEntities())) {
            $orderItems = $event->getEntities();
        } else if ($event->getEntities() instanceof OrderLineItemEntity) {
            $orderItems = array($event->getEntities());
        } else {
            return;
        }

        /*
         * @var OrderLineItemEntity[] $orderItems
         */
        foreach ($orderItems as $orderItem) {

            /*
             * @var OrderLineItemEntity $orderLineItem
             */
            $orderLineItem = $orderItem;

            if (\in_array(State::IS_DOWNLOAD, $orderLineItem->getStates(), true)) {
                $serials = $this->serialsRepo->getSerialsByOrderItemId($orderLineItem->getId(), $event->getContext());

                if (!empty($serials) && $serials->count() > 0) {
                    $orderLineItem->addExtension('lwsEsdSerials', $serials);
                }
            }
        }

    }

}
