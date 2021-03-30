<?php

namespace Gifty\Magento\Model\Config\Backend;

use Gifty\Client\GiftyClient;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;

class ApiKey extends Value
{

    /**
     * @return ApiKey
     * @throws ValidatorException
     */
    public function beforeSave(): ApiKey
    {
        $label = $this->getData('field_config/label');

        if ($this->getValue() === '') {
            throw new ValidatorException(__($label . ' is required.'));
        } elseif (is_string($this->getValue()) === false) {
            throw new ValidatorException(__($label . ' should be textual.'));
        }

        $giftyClient = new GiftyClient($this->getValue());

        if ($giftyClient->validateApiKey() === false) {
            throw new ValidatorException(__($label . ' is not a valid API key.'));
        }

        $this->setValue($this->getValue());

        return parent::beforeSave();
    }
}
