<?php

namespace Gifty\Magento\Plugin\SalesRule;

use Gifty\Magento\Helper\GiftCardHelper;
use Gifty\Magento\Helper\GiftyHelper;
use Magento\SalesRule\Model\Rule;

class RuleModelLoad
{
    private GiftyHelper $giftyHelper;
    private GiftCardHelper $giftCardHelper;

    public function __construct(
        GiftyHelper $giftyHelper,
        GiftCardHelper $giftCardHelper
    ) {
        $this->giftyHelper = $giftyHelper;
        $this->giftCardHelper = $giftCardHelper;
    }

    public function afterLoad(Rule $rule, Rule $result, $ruleId, $field = null): Rule
    {
        $this->giftyHelper->logger->debug('RuleModelLoad afterLoad logger');

        if ($result->getCouponCode() === null &&
            $field === null &&
            $ruleId !== null
        ) {
            $code = $this->giftyHelper->sanitizeCouponInput($ruleId);
            $giftCard = $this->giftCardHelper->getGiftCard($code);

            if ($giftCard !== null && $giftCard->isRedeemable()) {
                $rule = $this->giftCardHelper->getSalesRule($giftCard, $code);

                return $rule;
            }
        }

        return $result;
    }
}
