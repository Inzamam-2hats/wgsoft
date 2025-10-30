<?php declare(strict_types=1);

namespace EcomwiseOrderStatusComplete\Subscriber;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Exception\OrderTransactionNotFoundException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\MailTemplate\Exception\SalesChannelNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Event\StateMachineStateChangeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\Checkout\Cart\Exception\OrderDeliveryNotFoundException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Checkout\Order\OrderException;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Fabian Golle <fabian@golle-it.de>
 */
class OrderEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityRepository $orderRepository
     */
    private $orderRepository;

    /**
     * @var EntityRepository $orderTransactionRepository
     */
    private $orderTransactionRepository;

    /**
     * @var SystemConfigService $systemConfigService
     */
    private $systemConfigService;
    
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

     /**
     * @var StateMachineRegistry $stateMachineRegistry
     */
    private $stateMachineRegistry;

    /**
     * @var EntityRepository
     */
    private $deliveryRepository;
    
    /**
     * OrderEventSubscriber constructor.
     *
     * @param EntityRepository $orderRepository
     * @param EntityRepository $orderTransactionRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityRepository $orderRepository,
        EntityRepository $orderTransactionRepository,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger,
        StateMachineRegistry $stateMachineRegistry,
        EntityRepository $deliveryRepository
    ) {
        $this->orderRepository            = $orderRepository;
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->systemConfigService        = $systemConfigService;
        $this->logger                     = $logger;
        $this->stateMachineRegistry       = $stateMachineRegistry;
        $this->deliveryRepository         = $deliveryRepository;

    }

    public static function getSubscribedEvents(): array
    {
        return[
            'state_machine.order_transaction.state_changed' => 'onOrderTransactionStateChanged',
            'state_machine.order_delivery.state_changed' => 'onOrderDeliveryStateChange'
        ];
    }

    /**
     * Gets invoked when the payment status is changed.
     *
     * @param StateMachineStateChangeEvent $event
     *
     * @throws InconsistentCriteriaIdsException
     * @throws SalesChannelNotFoundException
     * @throws OrderTransactionNotFoundException
     */
    public function onOrderTransactionStateChanged(StateMachineStateChangeEvent $event)
    {
        $config = $this->systemConfigService->get('EcomwiseOrderStatusComplete.config');
        $nextState = $event->getNextState()->getTechnicalName();
        $context = $event->getContext();

        if($config["enableOrderStatusComplete"] && $event->getTransitionSide() == 'state_leave' && $nextState == 'paid') {
            $orderTransactionId = $event->getTransition()->getEntityId();
    
            /** @var OrderTransactionEntity|null $orderTransaction */
            $orderTransaction = $this->orderTransactionRepository->search(
                new Criteria([$orderTransactionId]),
                $event->getContext()
            )->first();
    
            if ($orderTransaction === null) {
                throw OrderException::orderTransactionNotFound($orderTransactionId);
            }
    
            $orderId = $orderTransaction->getOrderId();
    
            /** @var OrderEntity|null $order */  
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('order.id', $orderId))->addAssociation('deliveries')->addAssociation('deliveries.stateMachineState')->addAssociation('stateMachineState');
            $order = $this->orderRepository->search($criteria,$context)->first();
            $orderDeliveries = $order->getDeliveries()->getElements();
            $orderDeliveryState = '';
            foreach($orderDeliveries as $orderDelivery){
                $orderDeliveryState = $orderDelivery->getStateMachineState()->getTechnicalName();
            }

            if ($order instanceof OrderEntity && $orderDeliveryState == OrderDeliveryStates::STATE_SHIPPED) {
                $order_status = $order->getStateMachineState()->getTechnicalName();
                if($order_status == OrderStates::STATE_IN_PROGRESS) {
                    $transition = new Transition('order', $orderId, StateMachineTransitionActions::ACTION_COMPLETE, 'stateId');
                    $this->stateMachineRegistry->transition($transition, $context);
                } elseif($order_status == OrderStates::STATE_CANCELLED) {
                    $transition = new Transition('order', $orderId, StateMachineTransitionActions::ACTION_REOPEN, 'stateId');
                    $this->stateMachineRegistry->transition($transition, $context);
                    $transition = new Transition('order', $orderId, StateMachineTransitionActions::ACTION_PROCESS, 'stateId');
                    $this->stateMachineRegistry->transition($transition, $context);
                    $transition = new Transition('order', $orderId, StateMachineTransitionActions::ACTION_COMPLETE, 'stateId');
                    $this->stateMachineRegistry->transition($transition, $context);
                } elseif($order_status == 'open') {
                    $transition = new Transition('order', $orderId, StateMachineTransitionActions::ACTION_PROCESS, 'stateId');
                    $this->stateMachineRegistry->transition($transition, $context);
                    $transition = new Transition('order', $orderId, StateMachineTransitionActions::ACTION_COMPLETE, 'stateId');
                    $this->stateMachineRegistry->transition($transition, $context);
                }
            } else {
                $this->logger->error(sprintf('Event %s did not receive a proper ordernumber. Unable to get Order-object. Aborting.', $event->getName()));
            }
        }
    }

    public function onOrderDeliveryStateChange(StateMachineStateChangeEvent $event): void
    {
        $config = $this->systemConfigService->get('EcomwiseOrderStatusComplete.config');
        $nextState = $event->getNextState()->getTechnicalName();
        $orderDeliveryId = $event->getTransition()->getEntityId();
        $context = $event->getContext();

        if($config["enableOrderStatusComplete"] && $event->getTransitionSide() == 'state_enter' && $nextState == OrderDeliveryStates::STATE_SHIPPED) {
            /** @var OrderDeliveryEntity|null $orderDelivery */
            $orderDelivery = $this->deliveryRepository
                ->search(new Criteria([$orderDeliveryId]), $context)
                ->first();

            if ($orderDelivery === null) {
                throw OrderException::orderDeliveryNotFound($orderDeliveryId);
            }

            $orderId = $orderDelivery->getOrderId();
            $order = $this->getOrder($orderId, $context);
            $orderTransactions = $order->getTransactions()->getElements();
            $orderTransactionState = '';
            foreach($orderTransactions as $orderTransaction){
                $orderTransactionState = $orderTransaction->getStateMachineState()->getTechnicalName();
            }

            if ($orderTransactionState == 'paid') {
                $order_status = $order->getStateMachineState()->getTechnicalName();
                if($order_status == OrderStates::STATE_IN_PROGRESS) {
                    $transition = new Transition('order', $orderId, StateMachineTransitionActions::ACTION_COMPLETE, 'stateId');
                    $this->stateMachineRegistry->transition($transition, $context);
                } elseif($order_status == OrderStates::STATE_CANCELLED) {
                    $transition = new Transition('order', $orderId, StateMachineTransitionActions::ACTION_REOPEN, 'stateId');
                    $this->stateMachineRegistry->transition($transition, $context);
                    $transition = new Transition('order', $orderId, StateMachineTransitionActions::ACTION_PROCESS, 'stateId');
                    $this->stateMachineRegistry->transition($transition, $context);
                    $transition = new Transition('order', $orderId, StateMachineTransitionActions::ACTION_COMPLETE, 'stateId');
                    $this->stateMachineRegistry->transition($transition, $context);
                } elseif($order_status == 'open') {
                    $transition = new Transition('order', $orderId, StateMachineTransitionActions::ACTION_PROCESS, 'stateId');
                    $this->stateMachineRegistry->transition($transition, $context);
                    $transition = new Transition('order', $orderId, StateMachineTransitionActions::ACTION_COMPLETE, 'stateId');
                    $this->stateMachineRegistry->transition($transition, $context);
                }
            } else {
                $this->logger->error(sprintf('Event %s did not receive a proper ordernumber. Unable to get Order-object. Aborting.', $event->getName()));
            }
        }
    }

    /**
     * @throws OrderNotFoundException
     */
    private function getOrder(string $orderId, Context $context): OrderEntity
    {
        $orderCriteria = $this->getOrderCriteria($orderId);

        $order = $this->orderRepository
            ->search($orderCriteria, $context)
            ->first();

        if (!$order instanceof OrderEntity) {
            throw OrderException::orderNotFound($orderId);
        }

        return $order;
    }

    private function getOrderCriteria(string $orderId): Criteria
    {
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('orderCustomer.salutation');
        $criteria->addAssociation('stateMachineState');
        $criteria->addAssociation('lineItems');
        $criteria->addAssociation('transactions');
        $criteria->addAssociation('transactions.paymentMethod');
        $criteria->addAssociation('transactions.stateMachineState');
        $criteria->addAssociation('deliveries.shippingMethod');
        $criteria->addAssociation('deliveries.shippingOrderAddress.country');
        $criteria->addAssociation('deliveries.shippingOrderAddress.countryState');
        $criteria->addAssociation('deliveries.positions.orderLineItem');
        $criteria->addAssociation('deliveries.stateMachineState');
        $criteria->addAssociation('salesChannel');
        $criteria->addAssociation('orderCustomer.customer');
        $criteria->addAssociation('language.locale');
        $criteria->addAssociation('lineItems.downloads.media');
        $criteria->addAssociation('currency');
        $criteria->addAssociation('addresses.country');
        $criteria->addAssociation('addresses.countryState');
        $criteria->addAssociation('tags');

        return $criteria;
    }
}
