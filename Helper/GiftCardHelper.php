<?php

namespace Gifty\Magento\Helper;

use Gifty\Client\Exceptions\ApiException;
use Gifty\Client\GiftyClient;
use Gifty\Client\Resources\GiftCard;
use Gifty\Magento\Logger\GiftyLogger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;

class GiftCardHelper
{
    const GIFT_CARD_STRING_LENGTH = 16;

    /**
     * @var GiftyClient
     */
    public $client;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;
    /**
     * @var GiftyLogger
     */
    private $logger;
    /**
     * @var array
     */
    private $giftCards = [];
    /**
     * @var array
     */
    private $salesRules = [];

    public function __construct(
        RuleFactory $ruleFactory,
        ScopeConfigInterface $scopeConfig,
        GiftyLogger $giftyLogger
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->logger = $giftyLogger;
        $this->client = new GiftyClient((string) $scopeConfig->getValue('gifty/general/api_key'));
    }

    /**
     * To prevent multiple API requests in a single page request,
     * we store retrieved Gift Cards in this array.
     *
     * @param string $code
     * @return GiftCard|null
     */
    public function getGiftCard(string $code): ?GiftCard
    {
        $this->logger->debug('GiftCardHelper getGiftCard: ' . $code);

        if (property_exists((object) $this->giftCards, $code) === false) {
            $this->logger->debug('Fetching Gift Card from API: ' . $code);

            try {
                $this->giftCards[$code] = $this->client->giftCards->get($code);

                $this->logger->debug('Found Gift Card with balance of: ' . $this->giftCards[$code]->getBalance());
            } catch (ApiException $e) {
                $this->giftCards[$code] = null;

                $this->logger->debug('Could not find Gift Card');
            }
        }

        return $this->giftCards[$code];
    }

    public function getSalesRule(GiftCard $giftCard, string $code): Rule
    {
        if (isset($this->salesRules[$code]) === false) {
            $discount = $giftCard->getBalance() / 100;
            $rule = $this->ruleFactory->create();

            $this->salesRules[$code] = $rule
                ->setName(__('Gifty Gift Card'))
                ->setDescription(__('Gift Card %1', $code))
                ->setCouponCode($code)
                ->setCouponType(Rule::COUPON_TYPE_SPECIFIC)
                ->setStopRulesProcessing(0)
                ->setIsAdvanced(1)
                ->setSortOrder(1)
                ->setSimpleAction(Rule::CART_FIXED_ACTION)
                ->setDiscountAmount($discount)
                ->setApplyToShipping(0)
                ->setIsRss(0)
                ->setIsActive(1);
        }

        return $this->salesRules[$code];
    }
}
