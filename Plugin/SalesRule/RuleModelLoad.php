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

    public function afterLoad(Rule $rule, Rule $result, $ruleId, $field = null): Rule
    {
        if ($result->getCouponCode() === null &&
            $field === null &&
            $ruleId !== null &&
            strlen($ruleId) >= GiftCardHelper::GIFT_CARD_STRING_LENGTH
        ) {
            $this->giftyHelper->logger->debug('RuleModelLoad afterLoad');

            $code = $this->giftyHelper->sanitizeCouponInput($ruleId);

            if (strlen($code) !== GiftCardHelper::GIFT_CARD_STRING_LENGTH) {
                return $result;
            }

            $giftCard = $this->giftCardHelper->getGiftCard($code);

            if ($giftCard !== null && $giftCard->isRedeemable()) {
                $rule = $this->giftCardHelper->getSalesRule($giftCard, $code);

                return $rule;
            }
        }

        return $result;
    }
}
