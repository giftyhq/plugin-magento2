<?php

namespace Gifty\Magento\Plugin\SalesRule;

use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\SalesRule\Model\Rule;

class RuleModelLoad
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
     * After rule load plugin that checks if a non-existent rule could be a Gifty gift card
     *
     * @param Rule $rule Original rule model
     * @param Rule $result Result from load
     * @param mixed $ruleId Rule ID being loaded
     * @param string|null $field Field to load by
     * @return Rule
     */
    public function afterLoad(Rule $rule, Rule $result, $ruleId, $field = null): Rule
    {
        if ($result->getCouponCode() === null &&
            $field === null &&
            $ruleId !== null
        ) {
            $this->giftyHelper->logger->debug('RuleModelLoad afterLoad');

            $code = $this->giftyHelper->sanitizeCouponInput($ruleId);

            if (!$this->giftCardHelper->isValidGiftCardFormat($code)) {
                return $result;
            }

            $giftCard = $this->giftCardHelper->getGiftCard($code);

            if ($giftCard !== null && $giftCard->isRedeemable()) {
                return $this->giftCardHelper->getSalesRule($giftCard, $code);
            }
        }

        return $result;
    }
}
