<?php
declare(strict_types=1);

namespace Tmms\ProductCustomerInputs\Storefront\Controller;

    use Shopware\Core\Framework\Context;
    use Shopware\Core\Framework\Log\Package;
    use Shopware\Core\System\SalesChannel\SalesChannelContext;
    use Shopware\Storefront\Controller\StorefrontController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

    #[Route(defaults: ['_routeScope' => ['storefront']])]
    #[Package('storefront')]
    class SaveCustomerInputController extends StorefrontController
    {
        #[Route(path: '/savecustomerinputs/request', name: 'frontend.savecustomerinputs.request', options: ['seo' => false], defaults: ['id' => null, 'XmlHttpRequest' => true, '_routeScope' => ['storefront']], methods: ['GET', 'POST'])]
        public function request(Request $request, SalesChannelContext $context, Context $criteriaContext): Response
        {
            $count = $request->request->get('tmms-customer-input-count');
            $value = $request->request->get('tmms-customer-input-value-' . $count);
            $productNumber = $request->request->get('tmms-customer-input-productnumber-' . $count);
            $label = $request->request->get('tmms-customer-input-label-' . $count);
            $placeholder = $request->request->get('tmms-customer-input-placeholder-' . $count);
            $fieldType = $request->request->get('tmms-customer-input-fieldtype-' . $count);
            $session = $request->getSession();

            if ($fieldType === 'boolean' && !isset($value)) {
                $value = '0';
            }

            if ($productNumber) {
                $customerInputsArray = [];
                $customerInputsArray['tmms_customer_input_value'] = $value;
                $customerInputsArray['tmms_customer_input_label'] = $label;
                $customerInputsArray['tmms_customer_input_placeholder'] = $placeholder;
                $customerInputsArray['tmms_customer_input_fieldtype'] = $fieldType;

                $session->set('tmms_customer_input_' . $count . '_' . $productNumber, $customerInputsArray);
            }

            return new Response(json_encode(['success' => true]));
        }
    }
