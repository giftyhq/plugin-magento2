<?php


namespace Gifty\Magento\Plugin\Sales;

use Gifty\Client\Exceptions\ApiException;
use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\OrderService;

class TransactionRelease
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

    public function afterCancel(OrderService $subject, bool $result, $orderId): bool
    {
        $this->giftyHelper->logger->debug('afterCancel!');

        if (!$result) {
            return $result;
        }

        $order = $this->orderRepository->get($orderId);

        if ($order->getGiftyTransactionIdRedeem() === null) {
            return $result;
        }

        $giftCard = $this->giftCardHelper->getGiftCard($order->getGiftyGiftCardCode());
        $transaction = null;

        if ($giftCard !== null) {
            try {
                $transaction = $giftCard->transactions->release($order->getGiftyTransactionIdRedeem());
            } catch (ApiException $e) {
                $order->addCommentToStatusHistory(__(
                    "Failed to release transaction on Gift Card \"%1\". Transaction ID: \"%2\"",
                    $order->getGiftyGiftCardCode(),
                    $order->getGiftyTransactionIdRedeem()
                ));
            }
        }

        if ($transaction !== null) {
            $order->setGiftyTransactionIdRelease($transaction->getId());

            $order->addCommentToStatusHistory(__(
                "Released amount of %1 on Gift Card \"%2\". Transaction ID: \"%3\"",
                $this->giftyHelper->centsToCurrencyString($transaction->getAmount()),
                $order->getGiftyGiftCardCode(),
                $transaction->getId()
            ));
        }

        $this->orderRepository->save($order);

        return $result;
    }
}
