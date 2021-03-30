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
    public GiftyLogger $logger;

    private Escaper $escaper;
    private Data $pricingHelper;

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

        return $this->escaper
            ->escapeHtml($code);
    }
}
