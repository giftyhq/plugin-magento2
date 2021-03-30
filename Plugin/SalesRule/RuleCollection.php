<?php


namespace Gifty\Magento\Plugin\SalesRule;

use Gifty\Client\Exceptions\ApiException;
use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;

class RuleCollection
{
    /**
     * @var GiftyHelper
     */
    private $giftyHelper;
    /**
     * @var GiftCardHelper
     */
    private $giftCardHelper;

    /**
     * @var string|null
     */
    protected $couponCode = null;
    /**
     * @var bool
     */
    protected $passedRule = false;

    public function __construct(
        GiftyHelper $giftyHelper,
        GiftCardHelper $giftCardHelper
    ) {
        $this->giftyHelper    = $giftyHelper;
        $this->giftCardHelper = $giftCardHelper;
    }

    /**
     * In the afterLoad method we will hook in to add a sales rule if a match is found with the coupon code.
     * Because the coupon code is not available at that moment, we will apply it to this class here.
     *
     * @param Collection   $collection
     * @param              $websiteId
     * @param              $customerGroupId
     * @param string       $couponCode
     * @param null         $now
     * @param Address|null $address
     *
     * @return null
     */
    public function beforeSetValidationFilter(
        Collection $collection,
        $websiteId,
        $customerGroupId,
        $couponCode = '',
        $now = null,
        Address $address = null
    ) {
        $this->giftyHelper->logger->debug('RuleCollection beforeSetValidationFilter logger');

        if ($couponCode === '' || $couponCode === null) {
            return null;
        }

        $this->couponCode = $this->giftyHelper->sanitizeCouponInput($couponCode);

        return null;
    }

    public function afterLoad(Collection $collection, Collection $result) : Collection
    {
        if ($this->couponCode === null ||
            $this->passedRule === true
        ) {
            return $result;
        }

        $giftCard = $this->giftCardHelper->getGiftCard($this->couponCode);

        if ($giftCard === null || $giftCard->isRedeemable() === false) {
            return $result;
        }

        $rule = $this->giftCardHelper->getSalesRule($giftCard, $this->couponCode);
        $result->addItem($rule);

        $this->passedRule = true;

        return $result;
    }
}
