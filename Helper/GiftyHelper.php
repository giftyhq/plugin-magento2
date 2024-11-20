<?php


namespace Gifty\Magento\Helper;

use Gifty\Client\Resources\GiftCard;
use Gifty\Magento\Logger\GiftyLogger;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Pricing\Helper\Data;

class GiftyHelper extends AbstractHelper
{
    /**
     * @var GiftyLogger
     */
    public $logger;

    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var Data
     */
    private $pricingHelper;

    public function __construct(
        Context $context,
        Escaper $escaper,
        Data $pricingHelper,
        GiftyLogger $logger
    ) {
        parent::__construct($context);

        $this->escaper = $escaper;
        $this->pricingHelper = $pricingHelper;
        $this->logger = $logger;
    }

    public function centsToCurrencyString(int $cents): string
    {
        $value = $cents / 100;

        return $this->pricingHelper
            ->currency($value, true, false);
    }

    public function sanitizeCouponInput(string $code): string
    {
        $code = GiftCard::cleanCode($code);
        $code = strtoupper($code);

        return $this->escaper
            ->escapeHtml($code);
    }
}
