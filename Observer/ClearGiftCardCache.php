<?php

namespace Gifty\Magento\Observer;

use Gifty\Magento\Helper\SessionCache;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ClearGiftCardCache implements ObserverInterface
{
    private SessionCache $sessionCache;

    public function __construct(SessionCache $sessionCache)
    {
        $this->sessionCache = $sessionCache;
    }

    public function execute(Observer $observer)
    {
        $this->sessionCache->clearCache();
    }
}
