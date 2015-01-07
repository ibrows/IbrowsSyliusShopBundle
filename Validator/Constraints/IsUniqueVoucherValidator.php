<?php

namespace Ibrows\SyliusShopBundle\Validator\Constraints;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsUniqueVoucherValidator extends ConstraintValidator
{
    /**
     * @var ObjectRepository
     */
    protected $voucherRepository;

    /**
     * @var ObjectRepository
     */
    protected $percentVoucherRepository;

    /**
     * @param ObjectRepository $voucherRepository
     * @param ObjectRepository $percentVoucherRepository
     */
    public function __construct(ObjectRepository $voucherRepository, ObjectRepository $percentVoucherRepository)
    {
        $this->voucherRepository = $voucherRepository;
        $this->percentVoucherRepository = $percentVoucherRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if(
            $this->voucherRepository->findOneBy(array('code' => $value)) ||
            $this->percentVoucherRepository->findOneBy(array('code' => $value))
        ) {
            $this->context->addViolation(
                $constraint->message,
                array('%string%' => $value)
            );
        }
    }
}