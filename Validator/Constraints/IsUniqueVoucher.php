<?php

namespace Ibrows\SyliusShopBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsUniqueVoucher extends Constraint
{
    /**
     * @var string|null
     */
    public $errorPath = null;

    /**
     * @var string
     */
    public $message = 'The voucher "%string%" already exists: Voucher codes must be unique.';

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'validate_unique_voucher';
    }
}