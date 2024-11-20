<?php

declare(strict_types=1);

namespace Gifty\Magento\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_API_KEY           = 'gifty/general/api_key';
    public const XML_PATH_APPLY_TO_SHIPPING = 'gifty/general/apply_to_shipping';
    public const XML_PATH_GIFT_CARD_PATTERN = 'gifty/general/gift_card_pattern';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get API Key
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
     * Check if gift card should be applied to shipping
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
     * Get gift card pattern
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
