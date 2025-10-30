<?php
declare(strict_types=1);

namespace Tmms\ProductCustomerInputs;

    use Shopware\Core\Framework\Context;
    use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
    use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
    use Shopware\Core\Framework\Plugin;
    use Shopware\Core\Framework\Plugin\Context\ActivateContext;
    use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
    use Shopware\Core\Framework\Plugin\Context\InstallContext;
    use Shopware\Core\Framework\Plugin\Context\UninstallContext;
    use Shopware\Core\Framework\Plugin\Context\UpdateContext;
    use Shopware\Core\System\CustomField\CustomFieldTypes;

    class TmmsProductCustomerInputs extends Plugin
    {
        const CUSTOMER_INPUT_COUNT = 5;

        public function install(InstallContext $installContext): void
        {
            $this->addCustomFields($installContext->getContext());

            parent::install($installContext);
        }

        private function addCustomFields($installContext){
            $customFieldSetRepository = $this->container->get('custom_field_set.repository');

            for ($i = 1; $i <= TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT; ++$i) {
                $criteria = new Criteria();

                $criteria->addFilter(new EqualsFilter('name', 'tmms_customer_input_' . $i));

                $result = $customFieldSetRepository->searchIds($criteria, $installContext);

                if (!($result->getTotal() > 0)) {
                    $customFieldSetRepository->create([[
                        'name' => 'tmms_customer_input_' . $i,
                        'config' => [
                            'label' => [
                                'de-DE' => 'Eingabe ' . $i,
                                'en-GB' => 'input ' . $i,
                            ],
                        ],
                        'position' => $i,
                        'customFields' => [
                            [
                                'name' => 'tmms_customer_input_' . $i . '_active',
                                'type' => CustomFieldTypes::BOOL,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Eingabe aktivieren',
                                        'en-GB' => 'Activate input',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'checkbox',
                                    'customFieldPosition' => 1,
                                    'type' => 'checkbox',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_fieldtype',
                                'type' => CustomFieldTypes::SELECT,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Feldtyp der Eingabe',
                                        'en-GB' => 'type of the field of the input',
                                    ],
                                    'options' => [
                                        [
                                            'label' => [
                                                'de-DE' => 'einzeiliges Eingabefeld',
                                                'en-GB' => 'single-line input field',
                                            ],
                                            'value' => 'input',
                                        ],
                                        [
                                            'label' => [
                                                'de-DE' => 'mehrzeiliges Eingabefeld',
                                                'en-GB' => 'multi-line input field',
                                            ],
                                            'value' => 'textarea',
                                        ],
                                        [
                                            'label' => [
                                                'de-DE' => 'Nummernfeld',
                                                'en-GB' => 'number field',
                                            ],
                                            'value' => 'number',
                                        ],
                                        [
                                            'label' => [
                                                'de-DE' => 'Checkboxfeld',
                                                'en-GB' => 'boolean field',
                                            ],
                                            'value' => 'boolean',
                                        ],
                                        [
                                            'label' => [
                                                'de-DE' => 'Datums- und Uhrzeitfeld',
                                                'en-GB' => 'date and time field',
                                            ],
                                            'value' => 'datetime',
                                        ], [
                                            'label' => [
                                                'de-DE' => 'Datumsfeld',
                                                'en-GB' => 'date field',
                                            ],
                                            'value' => 'date',
                                        ],
                                        [
                                            'label' => [
                                                'de-DE' => 'Uhrzeitfeld',
                                                'en-GB' => 'time field',
                                            ],
                                            'value' => 'time',
                                        ],
                                        [
                                            'label' => [
                                                'de-DE' => 'Auswahlfeld',
                                                'en-GB' => 'select field',
                                            ],
                                            'value' => 'select',
                                        ],
                                    ],
                                    'componentName' => 'sw-single-select',
                                    'customFieldType' => 'select',
                                    'customFieldPosition' => 2,
                                    'type' => 'select',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_title',
                                'type' => CustomFieldTypes::TEXT,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Beschriftung oberhalb der Eingabe',
                                        'en-GB' => 'label above the input',
                                    ],
                                    'placeholder' => [
                                        'de-DE' => 'Beschriftung oberhalb der Eingabe, bspw. "Texteingabe:"',
                                        'en-GB' => 'label above the input, e.g. "text input:"',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'text',
                                    'customFieldPosition' => 3,
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_placeholder',
                                'type' => CustomFieldTypes::TEXT,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Platzhalter der Eingabe',
                                        'en-GB' => 'placeholder of the input',
                                    ],
                                    'placeholder' => [
                                        'de-DE' => 'Platzhalter der Eingabe, bspw. "Bitte geben Sie einen Wert ein"',
                                        'en-GB' => 'placeholder of the input, e.g. "please enter a value"',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'text',
                                    'customFieldPosition' => 4,
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_required',
                                'type' => CustomFieldTypes::BOOL,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Eingabe ist ein Pflichtfeld',
                                        'en-GB' => 'input is a required field',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'checkbox',
                                    'customFieldPosition' => 5,
                                    'type' => 'checkbox',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_minvalue',
                                'type' => CustomFieldTypes::INT,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Mindestwert der eingegebenen Zahl',
                                        'en-GB' => 'minimum value of the entered number',
                                    ],
                                    'placeholder' => [
                                        'de-DE' => '5',
                                        'en-GB' => '5',
                                    ],
                                    'helpText' => [
                                        'de-DE' => 'Der Mindestwert wird nur beim Feldtyp "Nummernfeld" verwendet und muss größer als 0 sein',
                                        'en-GB' => 'The minimum value is only used for the field type "number field" and must be greater than 0',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'number',
                                    'customFieldPosition' => 6,
                                    'type' => 'number',
                                    'numberType' => 'int',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_maxvalue',
                                'type' => CustomFieldTypes::INT,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Maximale Anzahl an Zeichen oder Maximalwert der eingegebenen Zahl',
                                        'en-GB' => 'maximum number of characters or maximum value of the entered number',
                                    ],
                                    'placeholder' => [
                                        'de-DE' => '20',
                                        'en-GB' => '20',
                                    ],
                                    'helpText' => [
                                        'de-DE' => 'Die "maximale Anzahl an Zeichen" wird bei den Feldtypen "einzeiliges Eingabefeld" und "mehrzeiliges Eingabefeld" und der "Maximalwert" beim Feldtyp "Nummernfeld" verwendet',
                                        'en-GB' => 'The maximum number of characters is used for the field types "single-line input field" and "multi-line input field" and the maximum value is used for the field type "number field"',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'number',
                                    'customFieldPosition' => 7,
                                    'type' => 'number',
                                    'numberType' => 'int',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_stepsvalue',
                                'type' => CustomFieldTypes::TEXT,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Schrittweite für das Feld',
                                        'en-GB' => 'steps for the field',
                                    ],
                                    'placeholder' => [
                                        'de-DE' => '5',
                                        'en-GB' => '5',
                                    ],
                                    'helpText' => [
                                        'de-DE' => 'Die Schrittweite wird nur beim Feldtyp "Nummernfeld" verwendet und muss bei einer Kommazahl das Trennzeichen "." ohne Anführungszeichen haben, beispielsweise 0.5. Zudem müssen die Felder "Mindestwert im Feld" und "Maximale Anzahl an Zeichen oder Maximalwert im Feld" einen positiven Wert haben.',
                                        'en-GB' => 'The steps are only used for the field type "number field" and must have the separator "." without quotes at a decimal point number, for example 0.5. In addition, the fields "minimum value in field" and "maximum number of characters or maximum value in field" must have a positive value.',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'text',
                                    'customFieldPosition' => 8,
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_startdate',
                                'type' => CustomFieldTypes::TEXT,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Startdatum des Datumsfeldes',
                                        'en-GB' => 'start date of the date field',
                                    ],
                                    'placeholder' => [
                                        'de-DE' => '+2 days',
                                        'en-GB' => '+2 days',
                                    ],
                                    'helpText' => [
                                        'de-DE' => 'Folgende Werte werden bspw. ohne eckige Klammern unterstützt: [today] (für heute), [+2 days] (für heute zzgl. eines festgelegten Zeitraums in der Form [+ 1 day], [+1 week], [+1 month] oder [+1 year]) oder [01.01.2021] (für ein festes Datum)',
                                        'en-GB' => 'For example the following values are supported without square brackets: [today] (for today), [+2 days] (for today plus a specified period in the form [+ 1 day], [+1 week], [+1 month] or [+1 year]) or [01.01.2021] (for a fixed date)',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'text',
                                    'customFieldPosition' => 9,
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_enddate',
                                'type' => CustomFieldTypes::TEXT,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Enddatum des Datumsfeldes',
                                        'en-GB' => 'end date of the date field',
                                    ],
                                    'placeholder' => [
                                        'de-DE' => '+2 days',
                                        'en-GB' => '+2 days',
                                    ],
                                    'helpText' => [
                                        'de-DE' => 'Folgende Werte werden bspw. ohne eckige Klammern unterstützt: [today] (für heute), [+2 days] (für heute zzgl. eines festgelegten Zeitraums in der Form [+ 1 day], [+1 week], [+1 month] oder [+1 year]) oder [31.12.2021] (für ein festes Datum)',
                                        'en-GB' => 'For example the following values are supported without square brackets: [today] (for today), [+2 days] (for today plus a specified period in the form [+ 1 day], [+1 week], [+1 month] or [+1 year]) or [31.12.2021] (for a fixed date)',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'text',
                                    'customFieldPosition' => 10,
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_disableddates',
                                'type' => CustomFieldTypes::TEXT,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Ausgeschlossene Daten für das Datumsfeld',
                                        'en-GB' => 'excluded dates for the date field',
                                    ],
                                    'placeholder' => [
                                        'de-DE' => '"01.04.2021","01.05.2021"',
                                        'en-GB' => '"01.04.2021","01.05.2021"',
                                    ],
                                    'helpText' => [
                                        'de-DE' => 'Folgende Werte werden bspw. ohne eckige Klammern unterstützt: ["01.01.2021"] (für ein ausgeschlossenes Datum), ["01.04.2021","01.05.2021"] (für mehrere ausgeschlossene Daten) [{"from": "02.04.2021", "to": "05.04.2021"},{"from": "03.05.2021", "to": "09.05.2021"}] (für mehrere Zeiträume) oder eine Kombination daraus',
                                        'en-GB' => 'For example the following values are supported without square brackets: ["01.01.2021"] (for one excluded date), ["01.04.2021", "01.05.2021"] (for several excluded dates) [{"from": "02.04.2021", "to": "05.04.2021"},{"from": "03.05.2021", "to": "09.05.2021"}] (for several periods) or a combination of these',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'text',
                                    'customFieldPosition' => 11,
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_starttime',
                                'type' => CustomFieldTypes::TEXT,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Startzeit des Uhrzeitfeldes',
                                        'en-GB' => 'start time of the time field',
                                    ],
                                    'placeholder' => [
                                        'de-DE' => '08:00',
                                        'en-GB' => '08:00',
                                    ],
                                    'helpText' => [
                                        'de-DE' => 'Folgender Wert wird ohne eckige Klammern unterstützt: [14:00] (für 14:00 Uhr)',
                                        'en-GB' => 'The following value is supported without square brackets: [14:00] (for 2:00 p.m.)',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'text',
                                    'customFieldPosition' => 12,
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_endtime',
                                'type' => CustomFieldTypes::TEXT,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Endzeit des Uhrzeitfeldes',
                                        'en-GB' => 'end time of the time field',
                                    ],
                                    'placeholder' => [
                                        'de-DE' => '17:00',
                                        'en-GB' => '17:00',
                                    ],
                                    'helpText' => [
                                        'de-DE' => 'Folgender Wert wird ohne eckige Klammern unterstützt: [20:00] (für 20:00 Uhr)',
                                        'en-GB' => 'The following value is supported without square brackets: [20:00] (for 8:00 p.m.)',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'text',
                                    'customFieldPosition' => 13,
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_daterange',
                                'type' => CustomFieldTypes::BOOL,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Zeitraumauswahl beim Datumsfeld möglich',
                                        'en-GB' => 'period selection is possible for the date field',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'checkbox',
                                    'customFieldPosition' => 14,
                                    'type' => 'checkbox',
                                ],
                            ],
                            [
                                'name' => 'tmms_customer_input_' . $i . '_selectfieldvalues',
                                'type' => CustomFieldTypes::TEXT,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Werte für das Auswahlfeld kommasepariert',
                                        'en-GB' => 'Values for the selection field separated by commas',
                                    ],
                                    'placeholder' => [
                                        'de-DE' => 'rot,gelb,blau',
                                        'en-GB' => 'red,yellow,blue',
                                    ],
                                    'helpText' => [
                                        'de-DE' => 'Folgender Wert wird ohne eckige Klammern unterstützt: [rot,gelb,blau]',
                                        'en-GB' => 'The following value is supported without square brackets: [red,yellow,blue]',
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'text',
                                    'customFieldPosition' => 15,
                                    'type' => 'text',
                                ],
                            ],
                        ],
                        'relations' => [
                            ['entityName' => 'product'],
                        ],
                    ]], $installContext);
                }
            }
        }

        public function postInstall(InstallContext $installContext): void
        {
            parent::postInstall($installContext);
        }

        public function update(UpdateContext $updateContext): void
        {
            if (\version_compare($updateContext->getCurrentPluginVersion(), '1.1.4', '<')) {
                $this->deleteCustomFields(Context::createDefaultContext());
                $this->addCustomFields(Context::createDefaultContext());
            }
        }

        public function postUpdate(UpdateContext $updateContext): void
        {
        }

        public function activate(ActivateContext $activateContext): void
        {
            parent::activate($activateContext);
        }

        public function deactivate(DeactivateContext $deactivateContext): void
        {
            parent::deactivate($deactivateContext);
        }

        public function uninstall(UninstallContext $uninstallContext): void
        {
            if ($uninstallContext->keepUserData()) {
                parent::uninstall($uninstallContext);

                return;
            }

            $this->deleteCustomFields($uninstallContext->getContext());

            parent::uninstall($uninstallContext);
        }

        private function deleteCustomFields($uninstallContext){
            $customFieldSetRepository = $this->container->get('custom_field_set.repository');

            for ($i = 1; $i <= TmmsProductCustomerInputs::CUSTOMER_INPUT_COUNT; ++$i) {
                $criteria = new Criteria();
                $criteria->addFilter(new EqualsFilter('name', 'tmms_customer_input_' . $i));

                $result = $customFieldSetRepository->searchIds($criteria, $uninstallContext);

                if ($result->getTotal() > 0) {
                    $data = $result->getDataOfId($result->firstId());
                    $customFieldSetRepository->delete([$data], $uninstallContext);
                }
            }
        }
    }
