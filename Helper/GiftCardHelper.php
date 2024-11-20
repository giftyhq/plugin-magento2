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
    private GiftyClient $client;
    private RuleFactory $ruleFactory;
    private GiftyLogger $logger;
    private Config      $config;
    private array       $salesRules = [];

    public function __construct(
        RuleFactory $ruleFactory,
        Config $config,
        GiftyLogger $giftyLogger
    ) {
        $this->ruleFactory  = $ruleFactory;
        $this->config       = $config;
        $this->logger       = $giftyLogger;
        $this->client       = new GiftyClient($this->config->getApiKey());
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

    /**
     * Get or create sales rule for gift card
     */
    public function getSalesRule(GiftCard $giftCard, string $code): Rule
    {
        if (isset($this->salesRules[$code])) {
            return $this->salesRules[$code];
        }

        $this->salesRules[$code] = $this->createSalesRule($giftCard, $code);
        return $this->salesRules[$code];
    }

    private function createSalesRule(GiftCard $giftCard, string $code): Rule
    {
        $discount = $giftCard->getBalance() / 100;

        return $this->ruleFactory
            ->create()
            ->setName(__('Gifty Gift Card'))
            ->setDescription(__('Gift Card %1', $code))
            ->setCouponCode($code)
            ->setCouponType(Rule::COUPON_TYPE_SPECIFIC)
            ->setStopRulesProcessing(0)
            ->setIsAdvanced(1)
            ->setSortOrder(1)
            ->setSimpleAction(Rule::CART_FIXED_ACTION)
            ->setDiscountAmount($discount)
            ->setApplyToShipping((int)$this->config->isApplyToShippingEnabled())
            ->setIsRss(0)
            ->setIsActive(1);
    }

    /**
     * Validate gift card code format
     *
     * @param string $code
     * @return bool
     */
    public function isValidGiftCardFormat(string $code): bool
    {
        $pattern = $this->config->getGiftCardPattern();
        $result  = @preg_match($pattern, $code);

        return $result === 1;
    }

    /**
     * Get client instance
     *
     * @return GiftyClient
     */
    public function getClient(): GiftyClient
    {
        return $this->client;
    }
}
