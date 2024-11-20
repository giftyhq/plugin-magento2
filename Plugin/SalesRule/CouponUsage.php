<?php

declare(strict_types=1);

namespace Gifty\Magento\Plugin\SalesRule;

use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;

/**
 * Plugin to prevent saving usage statistics for virtual gift card coupons
 */
class CouponUsage
{
    /**
     * @var GiftyHelper
     */
    private GiftyHelper $giftyHelper;
    /**
     * @var GiftCardHelper
     */
    private GiftCardHelper $giftCardHelper;

    /**
     * @param GiftyHelper $giftyHelper
     * @param GiftCardHelper $giftCardHelper
     */
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
     * @param Usage $instance Usage instance
     * @param mixed $customerId Customer ID
     * @param mixed $couponId Coupon ID
     * @param mixed $increment Whether to increment usage
     * @return array|null Modified parameters or null to skip
     */
    public function beforeUpdateCustomerCouponTimesUsed(
        Usage $instance,
        $customerId,
        $couponId,
        $increment = true
    ): ?array {
        if ($couponId === '' || $couponId === null) {
            return null;
        }

        $this->giftyHelper->logger->debug('CouponUsage beforeUpdateCustomerCouponTimesUsed');

        $code = $this->giftyHelper->sanitizeCouponInput($couponId);

        if (!$this->giftCardHelper->isValidGiftCardFormat($code)) {
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
