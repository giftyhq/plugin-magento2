<?php

declare(strict_types=1);

namespace Gifty\Magento\Model\Config\Backend;

use Gifty\Client\GiftyClient;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;

class ApiKey extends Value
{

    /**
     * Validate and set value before saving
     *
     * @return ApiKey
     * @throws ValidatorException
     */
    public function beforeSave(): ApiKey
    {
        $value = $this->getValue();
        $label = $this->getData('field_config/label');

        $this->validateValue($value, $label);
        $this->validateApiKey($value, $label);

        return parent::beforeSave();
    }

    /**
     * Validate value format
     *
     * @param mixed $value
     * @param string $label
     * @throws ValidatorException
     */
    private function validateValue(mixed $value, string $label): void
    {
        if ($value === '') {
            throw new ValidatorException(__('%1 is required.', $label));
        }

        if (!is_string($value)) {
            throw new ValidatorException(__('%1 should be textual.', $label));
        }
    }

    /**
     * Validate API key with Gifty service
     *
     * @param string $value
     * @param string $label
     * @throws ValidatorException
     */
    private function validateApiKey(string $value, string $label): void
    {
        try {
            $giftyClient = new GiftyClient($value);

            if (!$giftyClient->validateApiKey()) {
                throw new ValidatorException(__('%1 is not a valid API key.', $label));
            }
        } catch (\Exception $e) {
            throw new ValidatorException(
                __('Unable to validate %1: %2', $label, $e->getMessage())
            );
        }
    }
}
