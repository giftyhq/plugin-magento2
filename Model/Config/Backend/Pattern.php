<?php

declare(strict_types=1);

namespace Gifty\Magento\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;

class Pattern extends Value
{

    /**
     * @return Pattern
     * @throws ValidatorException
     */
    public function beforeSave(): Pattern
    {
        $value = $this->getValue();

        if (empty($value) === true) {
            return parent::beforeSave();
        }

        try {
            if (@preg_match($value, '') === false) {
                throw new ValidatorException(__('Invalid regular expression pattern'));
            }

            // Test if a valid gift card would still be accepted
            $testCodes = ['ACDE2456FGHJ679K', 'AAAACCCCDDDDEEEE'];

            foreach ($testCodes as $testCode) {
                if (@preg_match($value, $testCode) !== 1) {
                    throw new ValidatorException(
                        __('The pattern %1 is too restrictive. It would not accept valid gift card codes.', $value)
                    );
                }
            }

        } catch (\Exception $e) {
            throw new ValidatorException(
                __('Invalid regular expression pattern: %1', $e->getMessage())
            );
        }

        return parent::beforeSave();
    }
}
