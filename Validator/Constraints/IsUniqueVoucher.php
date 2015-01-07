<?php

namespace Ibrows\SyliusShopBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsUniqueVoucher extends Constraint
{
    public $message = 'The voucher "%string%" already exists: Voucher codes must be unique.';

    public function validatedBy()
    {
        return 'validate_unique_voucher';
    }


}