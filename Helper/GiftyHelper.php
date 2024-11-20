<?php

namespace Gifty\Magento\Helper;

use Gifty\Client\Resources\GiftCard;
use Gifty\Magento\Logger\GiftyLogger;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Pricing\Helper\Data;

/**
 * Helper class for common Gifty gift card operations
 *
 * Provides utility methods for handling gift card data formatting,
 * currency conversion, and input sanitization.
 *
 */
class GiftyHelper extends AbstractHelper
{
    /**
     * Logger instance for Gifty operations
     *
     * @var GiftyLogger
     */
    public GiftyLogger $logger;

    /**
     * HTML Escaper utility
     *
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * Magento pricing helper for currency formatting
     *
     * @var Data
     */
    private Data $pricingHelper;

    /**
     * Constructor
     *
     * @param Context $context Magento context object
     * @param Escaper $escaper HTML escaper utility
     * @param Data $pricingHelper Magento pricing helper
     * @param GiftyLogger $logger Gifty logger instance
     */
    public function __construct(
        Context $context,
        Escaper $escaper,
        Data $pricingHelper,
        GiftyLogger $logger
    ) {
        parent::__construct($context);

        $this->escaper       = $escaper;
        $this->pricingHelper = $pricingHelper;
        $this->logger        = $logger;
    }

    /**
     * Converts cents to a formatted currency string
     *
     * Takes an amount in cents and converts it to a properly formatted
     * currency string using the store's current currency settings.
     *
     * @param int $cents Amount in cents to convert
     * @return string Formatted currency string (e.g., "$10.00")
     */
    public function centsToCurrencyString(int $cents): string
    {
        $value = $cents / 100;

        return $this->pricingHelper
            ->currency($value, true, false);
    }

    /**
     * Sanitizes gift card coupon input
     *
     * Cleans and formats a gift card code by:
     * - Removing unwanted characters using GiftCard::cleanCode
     * - Converting to uppercase
     * - HTML escaping the result
     *
     * @param string $code Raw gift card code input
     * @return string Sanitized and formatted gift card code
     */
    public function sanitizeCouponInput(string $code): string
    {
        $code = GiftCard::cleanCode($code);
        $code = strtoupper($code);

        return $this->escaper
            ->escapeHtml($code);
    }
}
