<?php

namespace Gifty\Magento\Plugin\SalesRule;

use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Model\Coupon;

class CouponModelLoad
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
     * The coupon model load() method tries to fetch local coupons.
     * If a local coupon does not exist, it might be a Gifty gift card.
     *
     * @param Coupon $coupon
     * @param Coupon $result
     * @param        $modelId
     * @param null $field
     *
     * @return Coupon
     */
    public function afterLoad(Coupon $coupon, Coupon $result, $modelId, $field = null): Coupon
    {
        if ($result->getCouponId() === null &&
            $field === 'code' &&
            $modelId !== null
        ) {
            $code = $this->giftyHelper->sanitizeCouponInput($modelId);
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
