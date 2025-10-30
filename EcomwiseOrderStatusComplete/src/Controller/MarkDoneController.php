<?php declare(strict_types=1);

namespace EcomwiseOrderStatusComplete\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Framework\Context;
use Symfony\Component\HttpFoundation\JsonResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\System\StateMachine\Transition;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\StateMachine\StateMachineRegistry;

#[Route(defaults: ['_routeScope' => ['api']])]
class MarkDoneController extends AbstractController
{
    
    /**
     * @param EntityRepository $orderRepository
     * @param EntityRepository $systemConfigService
     * @param LoggerInterface $logger
     * @param EntityRepository $stateMachineRegistry
     */
    public function __construct(
        private EntityRepository $orderRepository,
        private SystemConfigService $systemConfigService,
        private LoggerInterface $logger,
        private StateMachineRegistry $stateMachineRegistry
    ) {
        $this->orderRepository            = $orderRepository;
        $this->systemConfigService        = $systemConfigService;
        $this->logger                     = $logger;
        $this->stateMachineRegistry       = $stateMachineRegistry;
    }

    #[Route(path: '/api/ecomwise/mark-done-api-action', name: 'api.action.ecomwise.mark-done-api-action', methods: ['POST'])]
    public function doneOrders(Request $request): JsonResponse
    {    
        $context = Context::createDefaultContext();
        $affectedIds = $request->request->all();
        $config = $this->systemConfigService->get('EcomwiseOrderStatusComplete.config');

        if($config["enableOrderStatusComplete"]) {
            foreach($affectedIds as $orderId){

                /** @var OrderEntity|null $order */  
                $criteria = new Criteria();
                $criteria->addFilter(new EqualsFilter('order.id', $orderId))->addAssociation('deliveries');
                $order = $this->orderRepository->search($criteria,$context)->first();

                if ($order instanceof OrderEntity) {
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
                    $this->logger->error(sprintf('orderStatusComplete.entity-components.errorMessage'));
                }
            }
        }

        return new JsonResponse(['type' => 'info', 'message' => 'orderStatusComplete.entity-components.infoMessage']);
  
    }
}
