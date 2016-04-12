<?php

namespace Ibrows\SyliusShopBundle\Validator\Constraints;

use Doctrine\Common\Persistence\ObjectRepository;
use Ibrows\SyliusShopBundle\Model\Voucher\BaseVoucherInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherInterface;
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
     * @param BaseVoucherInterface       $entity
     * @param IsUniqueVoucher|Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof BaseVoucherInterface) {
            return;
        }

        /** @var BaseVoucherInterface[] $alreadyExistings */
        $alreadyExistings = array();

        /** @var VoucherInterface $alreadyExisting */
        if ($alreadyExisting = $this->voucherRepository->findOneBy(array('code' => $entity->getCode()))) {
            $alreadyExistings[] = $alreadyExisting;
        }

        /** @var VoucherInterface $alreadyExisting */
        if ($alreadyExisting = $this->percentVoucherRepository->findOneBy(array('code' => $entity->getCode()))) {
            $alreadyExistings[] = $alreadyExisting;
        }

        foreach ($alreadyExistings as $alreadyExisting) {
            if (get_class($alreadyExisting) !== get_class($entity) || $alreadyExisting->getId() !== $entity->getId()) {
                $this->context->addViolationAt(
                    $constraint->errorPath,
                    $constraint->message,
                    array('%string%' => $entity->getCode())
                );
            }
        }
    }
}
