<?php

namespace Gifty\Magento\Observer;

use Gifty\Magento\Helper\GiftyHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;

class AttachCommentAfterQuoteSubmit implements ObserverInterface
{
    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;
    /**
     * @var GiftyHelper
     */
    private GiftyHelper $giftyHelper;

    /**
     * @param OrderRepository $orderRepository
     * @param GiftyHelper $giftyHelper
     */
    public function __construct(
        OrderRepository $orderRepository,
        GiftyHelper $giftyHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->giftyHelper     = $giftyHelper;
    }

    /**
     * In this Observer we attach a comment to the order after the quote has been submitted.
     *
     * @param Observer $observer
     * @return void
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function execute(
        Observer $observer
    ) {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');

        if ($order === null ||
            $order->hasData('gifty_gift_card_code') === false ||
            $order->hasData('gifty_transaction_id_redeem') === false ||
            $order->getData('gifty_gift_card_code') === null
        ) {
            $this->giftyHelper->logger->debug('Order does not have Gift Card data. Skipping comment attachment.');

            return;
        }

        $giftCardDiscount = abs((int)$order->getData('gifty_gift_card_discount'));

        if ($giftCardDiscount === 0) {
            $this->giftyHelper->logger->debug('Gift Card discount is 0. Skipping comment attachment.');

            return;
        }

        $order->addCommentToStatusHistory(__(
            "Reserved amount of %1 on Gift Card \"%2\". Transaction ID: \"%3\"",
            $this->giftyHelper->centsToCurrencyString(abs($giftCardDiscount)),
            $order->getData('gifty_gift_card_code'),
            $order->getData('gifty_transaction_id_redeem')
        ));

        $this->orderRepository->save($order);
    }
}
