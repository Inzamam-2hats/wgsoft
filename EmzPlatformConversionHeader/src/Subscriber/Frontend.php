<?php

namespace Emz\EmzPlatformConversionHeader\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoadedEvent;
use Shopware\Core\Framework\Struct\ArrayEntity;

class Frontend implements EventSubscriberInterface
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HeaderPageletLoadedEvent::class => 'onHeaderLoaded'
        ];
    }

    public function onHeaderLoaded(HeaderPageletLoadedEvent $event)
    {
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();
        $systemConfig = $this->systemConfigService->getDomain('EmzPlatformConversionHeader', $salesChannelId, true); 
        $page = $event->getPagelet();
            
        $page->addExtension('EmzConversionHeader', new ArrayEntity($systemConfig));
    }
}
