<?php

declare(strict_types=1);

namespace Gifty\Magento\Model\Config\Backend;

use Exception;
use Gifty\Client\GiftyClient;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;

/**
 * Backend model for validating the Gifty API key configuration
 */
class ApiKey extends Value
{
    /**
     * Validates API key format and authenticity before saving
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
     * Checks if API key value is non-empty and is a string
     *
     * @param mixed $value
     * @param string $label
     * @throws ValidatorException If value is empty or not a string
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
     * Tests API key validity by attempting to authenticate with Gifty service
     *
     * @param string $value
     * @param string $label
     * @throws ValidatorException If API key validation fails
     */
    private function validateApiKey(string $value, string $label): void
    {
        try {
            $giftyClient = new GiftyClient($value);

            if (!$giftyClient->validateApiKey()) {
                throw new ValidatorException(__('%1 is not a valid API key.', $label));
            }
        } catch (Exception $e) {
            throw new ValidatorException(
                __('Unable to validate %1: %2', $label, $e->getMessage())
            );
        }
    }
}
