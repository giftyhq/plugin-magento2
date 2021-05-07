<?php

namespace Gifty\Magento\Controller\Cart;

use Gifty\Magento\Logger\GiftyLogger;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Card extends \Magento\Framework\App\Action\Action
{
    /**
     * @var GiftyLogger
     */
    private $giftyLogger;


    /**
     * GiftCardPost constructor.
     */
//    public function __construct(Context $context, GiftyLogger $giftyLogger)
//    {
//        parent::__construct($context);
//
//        $this->giftyLogger = $giftyLogger;
//
//        $this->giftyLogger->debug('Constructor!');
//    }

    public function execute()
    {
        $this->_redirect('abc');
//        $this->giftyLogger->debug('Execute!');
echo "ddsdf";exit;
        die('a');
    }

//    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
//    {
//        return null;
//    }
//
//    public function validateForCsrf(RequestInterface $request): ?bool
//    {
//        return true;
//    }
}