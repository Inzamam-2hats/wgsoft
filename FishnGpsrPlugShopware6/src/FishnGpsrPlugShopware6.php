<?php declare(strict_types=1);

namespace Fishn\GpsrPlugShopware6;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class FishnGpsrPlugShopware6 extends Plugin
{

    public function install(InstallContext $installContext): void
    {
        if ($this->customFieldsCreated()) {
            return;
        }

        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $customFieldSetRepository->create([
            [
                'name' => 'fishn_gpsr_plugshopware6_cfs',
                'config' => [
                    'label' => [
                        'de-DE' => 'Herstellerdaten (GPSR)',
                        'en-GB' => 'Manufacturer data (GPSR)'
                    ]
                ],
                'customFields' => [
                    [
                        'name' => 'fishn_gpsr_plugshopware6_cfs_description',
                        'type' => CustomFieldTypes::HTML,
                        'config' => [
                            'label' => [
                                'de-DE' => 'Herstellerdaten (GPSR)',
                                'en-GB' => 'Manufacturer data (GPSR)'
                            ],
                            'componentName' => 'sw-text-editor',
                            'customFieldType' => 'textEditor',
                            'customFieldPosition' => 1,
                        ]
                    ],
                ],
                'relations' => [
                    [
                        'id' => Uuid::randomHex(),
                        'entityName' => 'product_manufacturer'
                    ]
                ]
            ]
        ], $installContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }
        $connection = $this->container->get(Connection::class);
        $connection->executeQuery('DELETE FROM `custom_field_set` WHERE name LIKE \'fishn_gpsr_plugshopware6_cfs\'');
    }

    /**
     * Check if custom fields are already created.
     * (If extension has been deleted and user decided to keep user data)
     *
     * @return bool
     */
    private function customFieldsCreated()
    {
        $connection = $this->container->get(Connection::class);
        return $connection
                ->executeQuery('SELECT * FROM `custom_field_set` WHERE name LIKE \'fishn_gpsr_plugshopware6_cfs\'')
                ->rowCount() > 0;
    }
}