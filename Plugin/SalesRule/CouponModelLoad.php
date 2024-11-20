<?php

declare(strict_types=1);

namespace Gifty\Magento\Plugin\SalesRule;

use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Model\Coupon;

/**
 * Plugin to handle loading of Gifty gift cards as virtual coupons
 */
class CouponModelLoad
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
        $this->giftyHelper    = $giftyHelper;
        $this->giftCardHelper = $giftCardHelper;
    }

    /**
     * Load Coupon model if Gifty gift card
     *
     * The coupon model load() method tries to fetch local coupons. If a local coupon
     * does not exist, it might be a Gifty gift card.
     *
     * @param Coupon $coupon Original coupon model
     * @param Coupon $result Result from load
     * @param mixed $modelId Model ID being loaded
     * @param string|null $field Field to load by
     * @return Coupon
     */
    public function afterLoad(Coupon $coupon, Coupon $result, $modelId, $field = null): Coupon
    {
        if ($result->getCouponId() === null &&
            $field === 'code' &&
            $modelId !== null
        ) {
            $code = $this->giftyHelper->sanitizeCouponInput($modelId);

            if (!$this->giftCardHelper->isValidGiftCardFormat($code)) {
                return $result;
            }

            $giftCard = $this->giftCardHelper->getGiftCard($code);

            if ($giftCard !== null && $giftCard->isRedeemable()) {
                $rule = $this->giftCardHelper->getSalesRule($giftCard, $code);

                $result
                    ->setId($code)
                    ->setCode($code)
                    ->setTimesUsed(0)
                    ->setType(CouponInterface::TYPE_MANUAL)
                    ->setRule($rule);
            }
        }

        return $result;
    }
}
