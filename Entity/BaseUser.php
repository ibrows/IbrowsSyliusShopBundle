<?php

namespace Ibrows\SyliusShopBundle\Entity;

use FOS\UserBundle\Entity\User as FOSUser;

use Doctrine\ORM\Mapping as ORM;

class BaseUser extends FOSUSer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @param string $email
     * @return BaseUser
     */
    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->setUsername($email);
        return $this;
    }
}