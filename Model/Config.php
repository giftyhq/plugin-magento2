<?php

declare(strict_types=1);

namespace Gifty\Magento\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration model for Gifty gift cards
 */
class Config
{
    /**
     * Configuration path for the Gifty API key
     *
     * @var string
     */
    public const XML_PATH_API_KEY = 'gifty/general/api_key';

    /**
     * Configuration path for applying gift cards to shipping
     *
     * @var string
     */
    public const XML_PATH_APPLY_TO_SHIPPING = 'gifty/general/apply_to_shipping';

    /**
     * Configuration path for the gift card validation pattern
     *
     * @var string
     */
    public const XML_PATH_GIFT_CARD_PATTERN = 'gifty/general/gift_card_pattern';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get the Gifty API key from configuration
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiKey(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_API_KEY
        );
    }

    /**
     * Check if gift cards should be applied to shipping costs
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isApplyToShippingEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_APPLY_TO_SHIPPING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get the regex pattern for gift card validation
     *
     * @param int|null $storeId
     * @return string
     */
    public function getGiftCardPattern(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_GIFT_CARD_PATTERN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: '/^[2456789ACDEFGHJKMNPQRSTUVWXYZ]{16}$/';
    }
}
