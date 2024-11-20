<?php

declare(strict_types=1);

namespace Gifty\Magento\Model\Config\Backend;

use Exception;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;

/**
 * Backend model for gift card pattern configuration validation
 */
class Pattern extends Value
{
    /**
     * Validates the gift card pattern before saving
     *
     * Checks if:
     * - The pattern is a valid regex
     * - The pattern accepts known valid gift card formats
     * - Empty values are allowed (uses default pattern)
     *
     * @return Pattern
     * @throws ValidatorException If pattern is invalid or too restrictive
     */
    public function beforeSave(): Pattern
    {
        $value = $this->getValue();

        if (empty($value) === true) {
            return parent::beforeSave();
        }

        try {
            if (preg_match($value, '') === false) {
                throw new ValidatorException(__('Invalid regular expression pattern'));
            }

            // Test if a valid gift card would still be accepted
            $testCodes = ['ACDE2456FGHJ679K', 'AAAACCCCDDDDEEEE'];

            foreach ($testCodes as $testCode) {
                $result = preg_match($value, $testCode);

                if ($result === false) {
                    throw new ValidatorException(__('Invalid regular expression pattern'));
                }
                if ($result !== 1) {
                    throw new ValidatorException(
                        __('The pattern %1 is too restrictive. It would not accept valid gift card codes.', $value)
                    );
                }
            }

        } catch (Exception $e) {
            throw new ValidatorException(
                __('Invalid regular expression pattern: %1', $e->getMessage())
            );
        }

        return parent::beforeSave();
    }
}
