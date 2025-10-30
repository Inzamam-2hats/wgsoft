<?php declare(strict_types=1);

namespace Fishn\GpsrPlugShopware6\Subscriber;

use AllowDynamicProperties;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Framework\Adapter\Cache\CacheClearer;

#[AllowDynamicProperties]
class ManufacturerCacheSubscriber implements EventSubscriberInterface
{
  public function __construct(CacheClearer $cacheClearer)
  {
    $this->cacheClearer = $cacheClearer;
  }

  public static function getSubscribedEvents(): array
  {
    return [
      ProductEvents::PRODUCT_MANUFACTURER_WRITTEN_EVENT => 'onManufacturerChange',
    ];
  }

  public function onManufacturerChange(EntityWrittenEvent $event): void
  {
    $this->cacheClearer->clear();
  }
}
