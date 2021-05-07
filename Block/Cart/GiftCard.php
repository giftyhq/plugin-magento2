<?php


namespace Gifty\Magento\Block\Cart;


class GiftCard extends \Magento\Framework\View\Element\Template
{
    public function sayHello()
    {
        return __('Hello World');
    }

    public function getGiftCardCode()
    {
        return '';
    }
}