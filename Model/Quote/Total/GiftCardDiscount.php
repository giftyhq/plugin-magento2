<?php

namespace Gifty\Magento\Model\Quote\Total;

use Gifty\Magento\Helper\GiftyHelper;

class GiftCardDiscount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $_priceCurrency;

    private $giftyHelper;

    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        GiftyHelper $giftyHelper
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->giftyHelper = $giftyHelper;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        // Todo: is this fix really necessary?
//        $items = $shippingAssignment->getItems();
//
//        if (count($items) === 0) {
//            return $this;
//        }

        $baseDiscount = 10.15;
        $discount = $this->_priceCurrency->convert($baseDiscount);

        $total->addTotalAmount('gifty_gift_card_discount', -$discount);
        $total->addBaseTotalAmount('gifty_gift_card_discount', -$baseDiscount);
//        $total->setTotalAmount('gifty_gift_card_discount', -$discount);
//        $total->setBaseTotalAmount('gifty_gift_card_discount', -$baseDiscount);

        $this->giftyHelper->logger->debug('Set discount in collect! TO: ' . $discount);

        $total->setGiftyGiftCardDiscount(-$discount);
        $total->setBaseGiftyGiftCardDiscount(-$baseDiscount);
        $quote->setGiftyGiftCardDiscount(-$discount);
        $quote->setBaseGiftyGiftCardDiscount(-$baseDiscount);

//        $total->setGrandTotal($total->getGrandTotal() - $discount);
//        $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscount);
//        $quote->setGiftyGiftCardDiscount(-$discount);

        return $this;
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        // Todo:  Total is = 0 here, why? Does the quote need saving? Or do we need to set the fee somewere else?
        // collectTotals seems to fix it, but is that the right way?

        $quote->collectTotals();

        // Of course that logic might change anyway when adding a custom gift card field (instead of voucher field now)
        $this->giftyHelper->logger->debug('Fetch called, discount is: ' . $total->getGiftyGiftCardDiscount());
        $this->giftyHelper->logger->debug('Fetch called, discount2 is: ' . $quote->getGiftyGiftCardDiscount());
        $this->giftyHelper->logger->debug(json_encode($total->getFullInfo()));
        $this->giftyHelper->logger->debug(json_encode($total->getAllBaseTotalAmounts()));
        $this->giftyHelper->logger->debug(json_encode($total->getTotalAmount('gifty_gift_card_discount')));

        return [
            'code' => 'gifty_gift_card_discount',
            'title' => 'Gift Card',
            'value' => $quote->getGiftyGiftCardDiscount()
        ];
    }

//    public function getLabel()
//    {
//        return __('Fee');
//    }

}