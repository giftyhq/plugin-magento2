<?php


namespace Gifty\Magento\Plugin\Sales;

use Gifty\Client\Exceptions\ApiException;
use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\OrderService;

/**
 * Handle gift card transaction release on order cancellation
 */
class TransactionRelease
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
        $this->giftyHelper = $giftyHelper;
        $this->giftCardHelper = $giftCardHelper;
    }

    /**
     * Releases gift card transaction when order is cancelled
     *
     * Attempts to release any held gift card amount when an order
     * is cancelled, ensuring funds are returned to the gift card.
     *
     * @param OrderService $subject
     * @param bool $result
     * @param int $orderId
     * @return bool
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function afterCancel(OrderService $subject, bool $result, int $orderId): bool
    {
        if (!$result) {
            return $result;
        }

        $order = $this->orderRepository->get($orderId);

        if ($order->getGiftyTransactionIdRedeem() === null) {
            return $result;
        }

        $this->giftyHelper->logger->debug('afterCancel');

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
