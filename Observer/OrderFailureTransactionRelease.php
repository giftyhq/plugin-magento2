<?php

namespace Gifty\Magento\Observer;

use Gifty\Client\Exceptions\ApiException;
use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;

/**
 * If order payment failed, or the order can not be completed for any other
 * reason we need to release the capture made on the gift card.
 */
class OrderFailureTransactionRelease implements ObserverInterface
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var GiftyHelper
     */
    private $giftyHelper;
    /**
     * @var GiftCardHelper
     */
    private $giftCardHelper;

    public function __construct(
        OrderRepository $orderRepository,
        GiftyHelper $giftyHelper,
        GiftCardHelper $giftCardHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->giftyHelper = $giftyHelper;
        $this->giftCardHelper = $giftCardHelper;
    }

    public function execute(Observer $observer)
    {
        $this->giftyHelper->logger->debug('Order failure event: sales_model_service_quote_submit_failure');

        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');

        if ($order->getGiftyTransactionIdRedeem() === null) {
            return;
        }

        $this->giftyHelper->logger->debug('Failed order has Gift Card transaction: ' .
            $order->getGiftyTransactionIdRedeem());

        if ($order->getGiftyTransactionIdRelease() !== null) {
            $this->giftyHelper->logger->debug('Gift Card already released. Transaction ID: ' .
                $order->getGiftyTransactionIdRelease());
        }

        $giftCard = $this->giftCardHelper->getGiftCard($order->getGiftyGiftCardCode());

        if ($giftCard !== null) {
            try {
                $transaction = $giftCard->transactions->release($order->getGiftyTransactionIdRedeem());
            } catch (ApiException $e) {
                $transaction = null;
            }
        }

        if ($giftCard === null || $transaction === null) {
            $this->giftyHelper->logger->debug(__(
                "Failed to release transaction on Gift Card \"%1\". Transaction ID: \"%2\". Order ID: \"%3\".",
                $order->getGiftyGiftCardCode(),
                $order->getGiftyTransactionIdRedeem(),
                $order->getId()
            ));

            return;
        }

        $this->giftyHelper->logger->debug('Released transaction. Transaction ID: ' . $transaction->getId());
        $order->addCommentToStatusHistory(__(
            "Released amount of %1 on Gift Card \"%2\". Transaction ID: \"%3\"",
            $this->giftyHelper->centsToCurrencyString($transaction->getAmount()),
            $order->getGiftyGiftCardCode(),
            $transaction->getId()
        ));
        $order->setGiftyTransactionIdRelease($transaction->getId());
        $this->orderRepository->save($order);
    }
}
