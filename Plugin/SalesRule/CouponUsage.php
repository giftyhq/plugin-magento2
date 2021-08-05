<?php


namespace Gifty\Magento\Plugin\SalesRule;

use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;

class CouponUsage
{
    /**
     * @var GiftyHelper
     */
    private $giftyHelper;
    /**
     * @var GiftCardHelper
     */
    private $giftCardHelper;

    public function __construct(
        GiftyHelper $giftyHelper,
        GiftCardHelper $giftCardHelper
    ) {
        $this->giftyHelper = $giftyHelper;
        $this->giftCardHelper = $giftCardHelper;
    }

    /**
     * The Gift Card sales rule is virtual, so saving coupon usage is not possible. We prevent this right here.
     *
     * @param Usage $instance
     * @param       $customerId
     * @param       $couponId
     * @param bool $increment
     *
     * @return array
     */
    public function beforeUpdateCustomerCouponTimesUsed(
        Usage $instance,
        $customerId,
        $couponId,
        $increment = true
    ): ?array {
        if ($couponId === '' || $couponId === null || strlen($couponId) < GiftCardHelper::GIFT_CARD_STRING_LENGTH) {
            return null;
        }

        $this->giftyHelper->logger->debug('CouponUsage beforeUpdateCustomerCouponTimesUsed');

        $code = $this->giftyHelper->sanitizeCouponInput($couponId);

        if(strlen($code) !== GiftCardHelper::GIFT_CARD_STRING_LENGTH) {
            return null;
        }

        $giftCard = $this->giftCardHelper->getGiftCard($code);
        $increment = false;

        if ($giftCard === null) {
            return null;
        }

        return [
            $customerId,
            $couponId,
            $increment
        ];
    }
}
