<?php
declare(strict_types=1);

namespace Tmms\ProductCustomerInputs\Storefront\Subscriber;

    use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
    use Shopware\Core\Content\Product\ProductEntity;
    use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
    use Shopware\Core\Framework\Context;
    use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
    use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
    use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
    use Shopware\Core\Framework\Struct\ArrayEntity;
    use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
    use Shopware\Core\System\SystemConfig\SystemConfigService;
    use Shopware\Storefront\Page\Account\Order\AccountOrderPageLoadedEvent;
    use Shopware\Storefront\Page\Account\Overview\AccountOverviewPageLoadedEvent;
    use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
    use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
    use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoadedEvent;
    use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoadedEvent;
    use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
    use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
    use Swag\CmsExtensions\Storefront\Pagelet\Quickview\QuickviewPageletLoadedEvent;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Contracts\Translation\TranslatorInterface;
    use Tmms\ProductCustomerInputs\TmmsProductCustomerInputs;

    class FrontendSubscriber implements EventSubscriberInterface
    {
        const PRODUCT_LINE_ITEM_TYPE = 'product';

        /**
         * @var TranslatorInterface
         */
        private $translator;

        /**
         * @var SystemConfigService
         */
        private $systemConfigService;

        /**
         * @var EntityRepository
         */
        private $orderLineItemRepository;

        /**
         * @var EntityRepository
         */
        private $salesChannelProductRepository;

        public function __construct(
            TranslatorInterface $translator,
            SystemConfigService $systemConfigService,
            EntityRepository $orderLineItemRepository,
            SalesChannelRepository $salesChannelProductRepository,
            private readonly RequestStack $requestStack
        ) {
            $this->translator = $translator;
            $this->systemConfigService = $systemConfigService;
            $this->orderLineItemRepository = $orderLineItemRepository;
            $this->salesChannelProductRepository = $salesChannelProductRepository;
        }

        public static function getSubscribedEvents(): array
        {
            if(class_exists('\\Swag\\CmsExtensions\\Storefront\\Pagelet\\Quickview\\QuickviewPageletLoadedEvent')) {
                return [
                    ProductPageLoadedEvent::class => 'onProductPageLoaded',
                    OffcanvasCartPageLoadedEvent::class => 'onOffcanvasCartPageLoaded',
                    CheckoutCartPageLoadedEvent::class => 'onCheckoutCartPageLoaded',
                    CheckoutConfirmPageLoadedEvent::class => 'onCheckoutConfirmPageLoaded',
                    CheckoutFinishPageLoadedEvent::class => 'onCheckoutFinishPageLoaded',
                    CheckoutOrderPlacedEvent::class => 'onCheckoutOrderPlaced',
                    AccountOrderPageLoadedEvent::class => 'onAccountOrderPageLoaded',
                    AccountOverviewPageLoadedEvent::class => 'onAccountOverviewPageLoaded',
                    NavigationPageLoadedEvent::class => 'onNavigationPageLoaded',
                    QuickviewPageletLoadedEvent::class => 'onQuickviewPageletLoaded',
                ];
            }else{
                return [
                    ProductPageLoadedEvent::class => 'onProductPageLoaded',
                    OffcanvasCartPageLoadedEvent::class => 'onOffcanvasCartPageLoaded',
                    CheckoutCartPageLoadedEvent::class => 'onCheckoutCartPageLoaded',
                    CheckoutConfirmPageLoadedEvent::class => 'onCheckoutConfirmPageLoaded',
                    CheckoutFinishPageLoadedEvent::class => 'onCheckoutFinishPageLoaded',
                    CheckoutOrderPlacedEvent::class => 'onCheckoutOrderPlaced',
                    AccountOrderPageLoadedEvent::class => 'onAccountOrderPageLoaded',
                    AccountOverviewPageLoadedEvent::class => 'onAccountOverviewPageLoaded',
                    NavigationPageLoadedEvent::class => 'onNavigationPageLoaded',
                ];
            }
        }

        /**
         * provide the customer input on the product detail page
         */
        public function onProductPageLoaded(ProductPageLoadedEvent $event): SalesChannelProductEntity
        {
            $customerInputShowOnProductDetailPage = $this->systemConfigService->get('TmmsProductCustomerInputs.config.customerInputShowOnProductDetailPage');

            $product = $event->getPage()->getProduct();
            $session = $event->getRequest()->getSession();

            if ($customerInputShowOnProductDetailPage == 'yes') {
                for ($i = 1; $i <= TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT; ++$i) {
                    $productExtensionData['value'] = $this->getCustomerInputFromSession($session, $product->getProductNumber(), 'value', $i);

                    $productExtension = $this->createArrayEntity($productExtensionData);

                    $product->addExtension('tmmsCustomerInput' . $i, $productExtension);
                }
            }

            $tmmsCustomerInputCount['value'] = TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT;

            $tmmsCustomerInputCountValue = $this->createArrayEntity($tmmsCustomerInputCount);

            $product->addExtension('tmmsCustomerInputCountValue', $tmmsCustomerInputCountValue);

            return $product;
        }

        /**
         * provide the customer input on the offcanvas cart page
         */
        public function onOffcanvasCartPageLoaded(OffcanvasCartPageLoadedEvent $event): array
        {
            return $this->getLineItemsCustomerInput($event);
        }

        /**
         * provide the customer input on the checkout cart page
         */
        public function onCheckoutCartPageLoaded(CheckoutCartPageLoadedEvent $event): array
        {
            return $this->getLineItemsCustomerInput($event);
        }

        /**
         * provide the customer input on the checkout confirm page
         */
        public function onCheckoutConfirmPageLoaded(CheckoutConfirmPageLoadedEvent $event): array
        {
            return $this->getLineItemsCustomerInput($event);
        }

        /**
         * save the customer inputs in the order line item custom fields after a successful order
         */
        public function onCheckoutFinishPageLoaded(CheckoutFinishPageLoadedEvent $event): void
        {
            $lineItems = $event->getPage()->getOrder()->getLineItems();

            $this->saveCustomerInputsInLineItemCustomFields($event, $lineItems, true);

            $event->getPage()->assign([
                'tmmsCustomerInputCountValue' => TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT,
            ]);
        }

        /**
         * save the customer inputs in the order line item custom fields when order is placed
         */
        public function onCheckoutOrderPlaced(CheckoutOrderPlacedEvent $event): void
        {
            $lineItems = $event->getOrder()->getLineItems();

            $this->saveCustomerInputsInLineItemCustomFields($event, $lineItems, false);
        }

        /**
         * assign the customer input count value to the account order page
         */
        public function onAccountOrderPageLoaded(AccountOrderPageLoadedEvent $event): void
        {
            $event->getPage()->assign([
                'tmmsCustomerInputCountValue' => TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT,
            ]);
        }

        /**
         * assign the customer input count value to the account overview page
         */
        public function onAccountOverviewPageLoaded(AccountOverviewPageLoadedEvent $event): void
        {
            $event->getPage()->assign([
                'tmmsCustomerInputCountValue' => TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT,
            ]);
        }

        /**
         * assign the customer input count value to the navigation page
         */
        public function onNavigationPageLoaded(NavigationPageLoadedEvent $event): void
        {
            $event->getPage()->assign([
                'tmmsCustomerInputCountValue' => TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT,
            ]);
        }

        /**
         * provide the customer input on the quickview
         * @param QuickviewPageletLoadedEvent
         */
        public function onQuickviewPageletLoaded($event): ProductEntity
        {
            $customerInputShowOnProductDetailPage = $this->systemConfigService->get('TmmsProductCustomerInputs.config.customerInputShowOnProductDetailPage');

            $product = $event->getPagelet()->getProduct();
            $session = $event->getRequest()->getSession();

            if ($customerInputShowOnProductDetailPage == 'yes') {
                for ($i = 1; $i <= TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT; ++$i) {
                    $productExtensionData['value'] = $this->getCustomerInputFromSession($session, $product->getProductNumber(), 'value', $i);

                    $productExtension = $this->createArrayEntity($productExtensionData);

                    $product->addExtension('tmmsCustomerInput' . $i, $productExtension);
                }
            }

            $tmmsCustomerInputCount['value'] = TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT;

            $tmmsCustomerInputCountValue = $this->createArrayEntity($tmmsCustomerInputCount);

            $product->addExtension('tmmsCustomerInputCountValue', $tmmsCustomerInputCountValue);

            return $product;
        }

        /**
         * save the customer inputs in the order line item custom fields
         */
        protected function saveCustomerInputsInLineItemCustomFields($event, $lineItems, $isCheckoutFinishPage): void
        {
            $customerInputTransferUnselectedCheckboxFieldsAsValue = $this->systemConfigService->get('TmmsProductCustomerInputs.config.customerInputTransferUnselectedCheckboxFieldsAsValue');

            if ($isCheckoutFinishPage) {
                $session = $event->getRequest()->getSession();
            } else {
                $mainRequest = $this->requestStack->getMainRequest();
                $session = $mainRequest->getSession();
            }

            foreach ($lineItems as $lineItem) {
                if ($lineItem->getType() === self::PRODUCT_LINE_ITEM_TYPE) {
                    $productNumber = $lineItem->getPayload()['productNumber'] ?? null;

                    if ($lineItem->getType() === self::PRODUCT_LINE_ITEM_TYPE && $productNumber) {
                        $tmmsCustomerInputValueArray = [];
                        $tmmsCustomerInputHasValue = 0;

                        for ($i = 1; $i <= TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT; ++$i) {
                            $tmmsCustomerInputValue = $this->getCustomerInputFromSession($session, $productNumber, 'value', $i);
                            $tmmsCustomerInputLabel = $this->getCustomerInputFromSession($session, $productNumber, 'label', $i);
                            $tmmsCustomerInputPlaceholder = $this->getCustomerInputFromSession($session, $productNumber, 'placeholder', $i);
                            $tmmsCustomerInputFieldType = $this->getCustomerInputFromSession($session, $productNumber, 'fieldtype', $i);

                            if ($tmmsCustomerInputValue || (($customerInputTransferUnselectedCheckboxFieldsAsValue) && ($tmmsCustomerInputFieldType == 'boolean') && ($tmmsCustomerInputValue == 0))) {
                                $tmmsCustomerInputValueArray[$i]['value'] = ($tmmsCustomerInputFieldType == 'boolean' ? ($tmmsCustomerInputValue == 1 ? $this->translator->trans('tmms.customerInput.selectedValue') : $this->translator->trans('tmms.customerInput.unselectedValue')) : $tmmsCustomerInputValue);
                                $tmmsCustomerInputValueArray[$i]['label'] = $tmmsCustomerInputLabel;
                                $tmmsCustomerInputValueArray[$i]['placeholder'] = $tmmsCustomerInputPlaceholder;
                                $tmmsCustomerInputValueArray[$i]['fieldtype'] = $tmmsCustomerInputFieldType;

                                $tmmsCustomerInputHasValue = $tmmsCustomerInputHasValue + 1;
                            } elseif ($customerInputTransferUnselectedCheckboxFieldsAsValue) {
                                if ($lineItem->getCustomFields() and (!isset($lineItem->getCustomFields()['tmms_customer_input_' . $i . '_value']))) {
                                    $productCustomFields = $lineItem->getPayload()['customFields'];

                                    if (isset($productCustomFields['tmms_customer_input_' . $i . '_active'])) {
                                        if ($productCustomFields['tmms_customer_input_' . $i . '_active'] && $productCustomFields['tmms_customer_input_' . $i . '_fieldtype'] == 'boolean') {
                                            $tmmsCustomerInputValue = $this->getCustomerInputFromSession($session, $productNumber, 'value', $i);

                                            if ($tmmsCustomerInputValue == '') {
                                                $tmmsCustomerInputValue = $this->translator->trans('tmms.customerInput.unselectedValue');
                                                $tmmsCustomerInputLabel = (isset($productCustomFields['tmms_customer_input_' . $i . '_title']) ? ($productCustomFields['tmms_customer_input_' . $i . '_title'] !== '' ? $productCustomFields['tmms_customer_input_' . $i . '_title'] : $this->translator->trans('tmms.customerInput.titleLabel')) : $this->translator->trans('tmms.customerInput.titleLabel'));
                                                $tmmsCustomerInputPlaceholder = (isset($productCustomFields['tmms_customer_input_' . $i . '_placeholder']) ? ($productCustomFields['tmms_customer_input_' . $i . '_placeholder'] !== '' ? $productCustomFields['tmms_customer_input_' . $i . '_placeholder'] : $this->translator->trans('tmms.customerInput.placeholderLabel')) : $this->translator->trans('tmms.customerInput.placeholderLabel'));
                                                $tmmsCustomerInputFieldType = $productCustomFields['tmms_customer_input_' . $i . '_fieldtype'];

                                                $tmmsCustomerInputValueArray[$i]['value'] = $tmmsCustomerInputValue;
                                                $tmmsCustomerInputValueArray[$i]['label'] = $tmmsCustomerInputLabel;
                                                $tmmsCustomerInputValueArray[$i]['placeholder'] = $tmmsCustomerInputPlaceholder;
                                                $tmmsCustomerInputValueArray[$i]['fieldtype'] = $tmmsCustomerInputFieldType;

                                                $tmmsCustomerInputHasValue = $tmmsCustomerInputHasValue + 1;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $tmmsCustomerInputValueCustomFieldArray = [];
                        $tmmsCustomerInputValueTypeArray = array('value', 'label', 'placeholder', 'fieldtype');

                        for ($i = 1; $i <= TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT; ++$i) {
                            foreach ($tmmsCustomerInputValueTypeArray as $ta) {
                                if (isset($tmmsCustomerInputValueArray[$i][$ta])) {
                                    $tmmsCustomerInputValueCustomFieldArray['tmms_customer_input_' . $i . '_' . $ta] = $tmmsCustomerInputValueArray[$i][$ta];
                                }
                            }
                        }

                        $lineItem->setCustomFields(
                            $tmmsCustomerInputValueCustomFieldArray
                        );

                        if ($isCheckoutFinishPage) {
                            for ($i = 1; $i <= TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT; ++$i) {
                                $this->removeCustomerInputFromSession($session, $productNumber, $i);
                            }
                        }

                        if ($tmmsCustomerInputHasValue > 0) {
                            $this->orderLineItemRepository->upsert(
                                [
                                    [
                                        'id' => $lineItem->getId(),
                                        'customFields' => $lineItem->getCustomFields(),
                                    ],
                                ],
                                $event->getContext() ?? Context::createDefaultContext()
                            );
                        }
                    }
                }
            }
        }

        /**
         * get the customer input for each line item
         */
        private function getLineItemsCustomerInput($event): array
        {
            $lineItems = $event->getPage()->getCart()->getLineItems()->getElements();
            $session = $event->getRequest()->getSession();

            foreach ($lineItems as $lineItem) {
                if ($lineItem->getType() === self::PRODUCT_LINE_ITEM_TYPE) {
                    $productNumber = $lineItem->getPayloadValue('productNumber') ?? null;
                    $productId = $lineItem->getReferencedId();

                    if ($lineItem->getType() === self::PRODUCT_LINE_ITEM_TYPE && $productNumber) {
                        $productCustomFields = $this->getProductCustomFields($event, $productId);
                        $lineItemExtensionProductCustomFields = $this->createArrayEntity($productCustomFields);
                        $lineItem->addExtension('tmmsLineItemProductCustomFields', $lineItemExtensionProductCustomFields);

                        for ($i = 1; $i <= TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT; ++$i) {
                            $productExtensionData['value'] = $this->getCustomerInputFromSession($session, $productNumber, 'value', $i);

                            $lineItemExtension = $this->createArrayEntity($productExtensionData);

                            $lineItem->addExtension('tmmsLineItemCustomerInput' . $i, $lineItemExtension);
                        }

                        $tmmsLineItemCustomerInputCount['value'] = TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT;

                        $tmmsLineItemCustomerInputCountValue = $this->createArrayEntity($tmmsLineItemCustomerInputCount);

                        $lineItem->addExtension('tmmsLineItemCustomerInputCountValue', $tmmsLineItemCustomerInputCountValue);
                    }
                }
            }

            return $lineItems;
        }

        /**
         * create an ArrayEntity with the given data
         */
        private function createArrayEntity($extensionData): ArrayEntity
        {
            return new ArrayEntity($extensionData);
        }

        /**
         * get the customer input from the session based on the product number
         */
        private function getCustomerInputFromSession($session, $productNumber, $type, $count): string
        {
            $sessionCustomerInput = '';
            $tmmsCustomerInput = sprintf('tmms_customer_input_%d_%s', $count, $productNumber);

            if ($session->has($tmmsCustomerInput)) {
                if (in_array($type, ['value', 'label', 'placeholder', 'fieldtype'])) {
                    $sessionCustomerInput = $session->get($tmmsCustomerInput)['tmms_customer_input_' . $type];
                }

                if ($type == 'value' && $sessionCustomerInput == null) {
                    $sessionCustomerInput = '';
                }
            }

            return $sessionCustomerInput;
        }

        /**
         * removes the customer input from the session based on the product number
         */
        private function removeCustomerInputFromSession($session, $productNumber, $count): void
        {
            $session->remove('tmms_customer_input_' . $count . '_' . $productNumber);
        }

        /**
         * get the product customFields based on the product id
         */
        private function getProductCustomFields($event, $productId): array
        {
            $productCustomFields = [];
            $productCriteria = new Criteria();

            $productCriteria->addFilter(
                new EqualsFilter('id', (string) $productId)
            );

            $products = $this->salesChannelProductRepository->search($productCriteria, $event->getSalesChannelContext());

            foreach ($products->getElements() as $product) {
                $productCustomFields = $product->getCustomFields();
            }

            return $productCustomFields;
        }
    }
