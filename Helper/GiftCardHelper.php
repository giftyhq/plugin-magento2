<?php

declare(strict_types=1);

namespace Gifty\Magento\Helper;

use Exception;
use Gifty\Client\Exceptions\ApiException;
use Gifty\Client\GiftyClient;
use Gifty\Client\Resources\GiftCard;
use Gifty\Magento\Logger\GiftyLogger;
use Gifty\Magento\Model\Config;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;

/**
 * Helper class for Gifty gift card operations
 *
 * Handles gift card validation, retrieval, and sales rule creation for the Gifty
 * gift card integration. Implements caching to prevent redundant API calls and
 * manages the conversion of gift cards to Magento sales rules.
 *
 */
class GiftCardHelper
{
    /**
     * Gifty API client instance
     *
     * @var GiftyClient
     */
    private GiftyClient $client;

    /**
     * Magento sales rule factory
     *
     * @var RuleFactory
     */
    private RuleFactory $ruleFactory;

    /**
     * Gifty logger instance
     *
     * @var GiftyLogger
     */
    private GiftyLogger $logger;

    /**
     * Gifty configuration model
     *
     * @var Config
     */
    private Config $config;

    /**
     * Session cache handler
     *
     * @var SessionCache
     */
    private SessionCache $sessionCache;

    /**
     * Cache of created sales rules indexed by gift card code
     *
     * @var array<string, Rule>
     */
    private array $salesRules = [];

    /**
     * Constructor
     *
     * Initializes helper with required dependencies and creates Gifty API client instance
     *
     * @param RuleFactory $ruleFactory Factory for creating sales rules
     * @param Config $config Gifty configuration model
     * @param GiftyLogger $giftyLogger Logger for Gifty operations
     * @param SessionCache $sessionCache Cache handler for gift card data
     */
    public function __construct(
        RuleFactory $ruleFactory,
        Config $config,
        GiftyLogger $giftyLogger,
        SessionCache $sessionCache
    ) {
        $this->ruleFactory  = $ruleFactory;
        $this->config       = $config;
        $this->logger       = $giftyLogger;
        $this->sessionCache = $sessionCache;
        $this->client       = new GiftyClient($this->config->getApiKey());
    }

    /**
     * Retrieves a gift card by its code
     *
     * First checks the session cache for the gift card to prevent unnecessary API calls.
     * If not found in cache, makes an API request and caches the result.
     * Failed API requests are also cached to prevent repeated failures.
     *
     * @param string $code Gift card code to retrieve
     * @return GiftCard|null The gift card if found and valid, null otherwise
     */
    public function getGiftCard(string $code): ?GiftCard
    {
        $cachedGiftCard = $this->sessionCache->getCachedGiftCard($code);
        if ($cachedGiftCard !== null) {
            $this->logger->debug('GiftCardHelper getGiftCard: ' . $code . ' (cached)');

            return $cachedGiftCard;
        }

        try {
            $giftCard = $this->client->giftCards->get($code);
            $this->sessionCache->cacheGiftCard($code, $giftCard);
            $this->logger->debug('GiftCardHelper getGiftCard: ' . $code . ' (API)');

            return $giftCard;
        } catch (ApiException $e) {
            $this->sessionCache->cacheGiftCard($code, null);
            $this->logger->error('GiftCardHelper getGiftCard: ' . $code . ' (API error)', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Gets or creates a sales rule for a gift card
     *
     * Returns an existing sales rule if one exists for the given code,
     * otherwise creates a new one and caches it.
     *
     * @param GiftCard $giftCard Gift card to create rule for
     * @param string $code Gift card code
     * @return Rule Sales rule for applying the gift card discount
     */
    public function getSalesRule(GiftCard $giftCard, string $code): Rule
    {
        if (isset($this->salesRules[$code])) {
            return $this->salesRules[$code];
        }

        $this->salesRules[$code] = $this->createSalesRule($giftCard, $code);
        return $this->salesRules[$code];
    }

    /**
     * Creates a new sales rule for a gift card
     *
     * Configures a Magento sales rule to apply the gift card balance as a discount.
     * The rule is set up as a cart-fixed discount that can optionally apply to shipping.
     *
     * @param GiftCard $giftCard Gift card to create rule for
     * @param string $code Gift card code
     * @return Rule Configured sales rule
     */
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
     * Validates a gift card code format
     *
     * Checks if a given code matches the configured gift card pattern.
     * Handles potential regex errors gracefully.
     *
     * @param string $code Code to validate
     * @return bool True if the code format is valid, false otherwise
     */
    public function isValidGiftCardFormat(string $code): bool
    {
        $pattern = $this->config->getGiftCardPattern();
        try {
            $result = preg_match($pattern, $code);
            if ($result === false) {
                return false;
            }
            return $result === 1;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Gets the Gifty API client instance
     *
     * @return GiftyClient The configured API client
     */
    public function getClient(): GiftyClient
    {
        return $this->client;
    }
}
