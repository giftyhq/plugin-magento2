<?php

namespace Gifty\Magento\Observer;

use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Copies gift card data from quote to order before order creation
 */
class SaveOrderBeforeSalesModelQuoteObserver implements ObserverInterface
{
    /**
     * @var Copy
     */
    private Copy $objectCopyService;

    /**
     * @param Copy $objectCopyService
     */
    public function __construct(Copy $objectCopyService)
    {
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Transfers gift card data from quote to order using fieldset mappings
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);
    }
}
