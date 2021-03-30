<?php

namespace Gifty\Magento\Plugin\Sales;

use Gifty\Client\Exceptions\ApiException;
use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order as ResourceModelOrder;

class TransactionCapture
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

    public function afterSave(ResourceModelOrder $subject, $result, Order $order)
    {
        if ($order->getState() !== Order::STATE_COMPLETE ||
            $order->getGiftyTransactionIdRedeem() === null ||
            $order->getGiftyTransactionIdCapture() !== null
        ) {
            return $result;
        }

        $giftCard = $this->giftCardHelper->getGiftCard($order->getGiftyGiftCardCode());
        $transaction = null;

        if ($giftCard !== null) {
            try {
                $transaction = $giftCard->transactions->capture($order->getGiftyTransactionIdRedeem());
            } catch (ApiException $e) {
                $order->addCommentToStatusHistory(__(
                    "Failed to capture transaction on Gift Card \"%1\". Transaction ID: \"%2\"",
                    $order->getGiftyGiftCardCode(),
                    $order->getGiftyTransactionIdRedeem()
                ));
            }
        }

        if ($transaction !== null) {
            $order->setGiftyTransactionIdCapture($transaction->getId());

            $order->addCommentToStatusHistory(__(
                "Captured amount of %1 on Gift Card \"%2\". Transaction ID: \"%3\"",
                $this->giftyHelper->centsToCurrencyString(abs($transaction->getAmount())),
                $order->getGiftyGiftCardCode(),
                $transaction->getId()
            ));
        }

        $this->orderRepository->save($order);

        return $result;
    }
}
