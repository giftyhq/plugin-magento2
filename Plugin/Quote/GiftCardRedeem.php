<?php


namespace Gifty\Magento\Plugin\Quote;

use Gifty\Client\Exceptions\ApiException;
use Gifty\Client\Exceptions\MissingParameterException;
use Gifty\Client\Resources\Transaction;
use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\QuoteRepository;

/**
 * Handle gift card redemption during order placement
 */
class GiftCardRedeem
{
    /**
     * @var QuoteRepository
     */
    private QuoteRepository $quoteRepository;
    /**
     * @var GiftyHelper
     */
    private GiftyHelper $giftyHelper;
    /**
     * @var GiftCardHelper
     */
    private GiftCardHelper $giftCardHelper;

    /**
     * @param QuoteRepository $quoteRepository
     * @param GiftyHelper $giftyHelper
     * @param GiftCardHelper $giftCardHelper
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        GiftyHelper $giftyHelper,
        GiftCardHelper $giftCardHelper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->giftyHelper     = $giftyHelper;
        $this->giftCardHelper  = $giftCardHelper;
    }

    /**
     * If the quote contains a coupon, we need to check if it is a Gifty gift card.
     * Is this the case? Then we'll calculate the amount that needs to be subtracted
     * from the gift card applied. It is important to note that other sales rules can
     * be applied to this order also.
     *
     * @param QuoteManagement $subject
     * @param Quote $quote
     * @param array $orderData
     *
     * @return array|null
     * @throws NoSuchEntityException
     */
    public function beforeSubmit(QuoteManagement $subject, Quote $quote, array $orderData = []): ?array
    {
        if ($quote->getCouponCode() === '' || $quote->getCouponCode() === null) {
            return null;
        }

        $couponCode = $this->giftyHelper->sanitizeCouponInput($quote->getCouponCode());

        if (!$this->giftCardHelper->isValidGiftCardFormat($couponCode)) {
            return null;
        }

        $giftCard = $this->giftCardHelper->getGiftCard($couponCode);

        // Not a Gifty gift card
        if ($giftCard === null) {
            return null;
        }

        $this->giftyHelper->logger->debug('beforeSubmit');

        $calculationQuote = clone $quote;
        $calculationQuote->setId(null);

        $calculationQuote->setCouponCode(null);
        $calculationQuote->setTotalsCollectedFlag(false);
        $calculationQuote->collectTotals();

        $totalWithoutGiftCard = $calculationQuote->getGrandTotal() * 100;
        $totalWithGiftCard    = $quote->getGrandTotal() * 100;
        $giftCardDiscount     = $totalWithoutGiftCard - $totalWithGiftCard;

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $quote->getShippingAddress()->setCollectShippingRates(false);
        $quote->getShippingAddress()->collectShippingRates();
        $quote->getShippingAddress()->getAllShippingRates();
        $quote->setTotalsCollectedFlag(false);

        try {
            $this->giftyHelper->logger->debug('beforeSubmit API redeem gift card');
            $redeemTransaction = $this->giftCardHelper->getClient()->giftCards->redeem($couponCode, [
                'amount'   => $giftCardDiscount,
                'currency' => 'EUR',
                'capture'  => false
            ]);
        } catch (MissingParameterException|ApiException $e) {
            throw new NoSuchEntityException(__(
                "The coupon code isn't valid. Verify the code and try again. %1",
                $e->getMessage()
            ));
        }

        $quote->setGiftyGiftCardCode($couponCode);
        $quote->setGiftyGiftCardDiscount(abs($redeemTransaction->getAmount()));
        $quote->setGiftyTransactionIdRedeem($redeemTransaction->getId());

        $this->quoteRepository->save($quote);

        return [$quote, $orderData];
    }
}
