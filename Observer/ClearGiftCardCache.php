<?php

namespace Gifty\Magento\Observer;

use Gifty\Magento\Helper\SessionCache;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observer that clears the gift card cache when cart changes occur
 */
class ClearGiftCardCache implements ObserverInterface
{
    /**
     * @var SessionCache
     */
    private SessionCache $sessionCache;

    /**
     * @param SessionCache $sessionCache
     */
    public function __construct(SessionCache $sessionCache)
    {
        $this->sessionCache = $sessionCache;
    }

    /**
     * Clears the gift card cache when cart is modified
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        $this->sessionCache->clearCache();
    }
}
