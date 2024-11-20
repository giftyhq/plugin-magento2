<?php

namespace Gifty\Magento\Observer;

use Gifty\Client\Exceptions\ApiException;
use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;

/**
 * Releases gift card transaction holds when order processing fails
 */
class OrderFailureTransactionRelease implements ObserverInterface
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
     * @var GiftCardHelper
     */
    private GiftCardHelper $giftCardHelper;

    /**
     * @param OrderRepository $orderRepository
     * @param GiftyHelper $giftyHelper
     * @param GiftCardHelper $giftCardHelper
     */
    public function __construct(
        OrderRepository $orderRepository,
        GiftyHelper $giftyHelper,
        GiftCardHelper $giftCardHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->giftyHelper     = $giftyHelper;
        $this->giftCardHelper  = $giftCardHelper;
    }

    /**
     * Releases held gift card amount when order fails
     *
     * Retrieves the gift card transaction and attempts to release the held amount.
     * Logs the release attempt and updates order status history.
     *
     * @param Observer $observer
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
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
            $this->giftyHelper->logger->critical(__(
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
