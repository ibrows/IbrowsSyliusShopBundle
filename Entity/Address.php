<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Model\Address\AddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Ibrows\SyliusShopBundle\Model\User\UserInterface;
use Ibrows\Bundle\SonataAdminAnnotationBundle\Annotation as Sonata;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_address")
 * @Sonata\Order\FormMapperAll
 * @Sonata\Order\ShowMapperAll
 * @ORM\InheritanceType("JOINED")
 */
class Address implements InvoiceAddressInterface, DeliveryAddressInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Sonata\Order\FormMapperExclude
     * @Sonata\ListMapper(identifier=true)
     */
    protected $id;

    /**
     * @var string $firstname
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"sylius_wizard_address"})
     * @Sonata\ListMapper()
     * @Sonata\DatagridMapper
     */
    protected $firstname;

    /**
     * @var string $lastname
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"sylius_wizard_address"})
     * @Sonata\ListMapper()
     * @Sonata\DatagridMapper
     */
    protected $lastname;

    /**
     * @var string $title
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"sylius_wizard_address"})
     */
    protected $title;

    /**
     * @var string $company
     * @ORM\Column(type="string", nullable=true)
     */
    protected $company;

    /**
     * @var string $street
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"sylius_wizard_address"})
     */
    protected $street;

    /**
     * @var string $zip
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"sylius_wizard_address"})
     * @Sonata\DatagridMapper
     * @Sonata\ListMapper()
     */
    protected $zip;

    /**
     * @var string $city
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"sylius_wizard_address"})
     * @Sonata\DatagridMapper
     * @Sonata\ListMapper()
     */
    protected $city;

    /**
     * @var string $country
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Country(groups={"sylius_wizard_address"})
     * @Assert\NotBlank(groups={"sylius_wizard_address"})
     */
    protected $country;

    /**
     * @var string $phone
     * @ORM\Column( type="string", nullable=true)
     */
    protected $phone;

    /**
     * @var string $email
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Email(groups={"sylius_wizard_address"})
     * @Sonata\ListMapper()
     * @Sonata\DatagridMapper
     */
    protected $email;


    /**
     * @var UserInterface
     * @ORM\ManyToOne(targetEntity="\Ibrows\SyliusShopBundle\Model\User\UserInterface", inversedBy="addresses")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Sonata\DatagridMapper
     * @Sonata\Order\FormMapperExclude
     */
    protected $user;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $city
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $company
     * @return Address
     */
    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $country
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $email
     * @return Address
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $firstname
     * @return Address
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $lastname
     * @return Address
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $phone
     * @return Address
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $street
     * @return Address
     */
    public function setStreet($street)
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $zip
     * @return Address
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
        return $this;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    protected function getStringTemplate()
    {
        return "%%Company%%
        %%Firstname%% %%Lastname%%
        %%Street%%
        %%Zip%% %%City%%";
    }

    protected function getStringTemplateVariables()
    {
        $vars = get_object_vars($this);
        return $vars;
    }

    public function __toString()
    {
        $template = $this->getStringTemplate();

        foreach ($this->getStringTemplateVariables() as $var => $value) {
            $var = ucfirst($var);
            if(method_exists($this, 'get' .  $var)){
                $value = call_user_func(array($this, 'get' .  $var));
            }
            $template = preg_replace('!%%' . $var . '%%!', $value, $template);
        }
        return preg_replace("!\n+\s*\n+!","\n",$template);
    }

    /**
     * @param AddressInterface $address
     * @return bool
     */
    public function compare(AddressInterface $address)
    {
        return(
            $this->getCity() == $address->getCity() &&
            $this->getCompany() == $address->getCompany() &&
            $this->getCountry() == $address->getCountry() &&
            $this->getEmail() == $address->getEmail() &&
            $this->getFirstname() == $address->getFirstname() &&
            $this->getPhone() == $address->getPhone() &&
            $this->getLastname() == $address->getLastname() &&
            $this->getStreet() == $address->getStreet() &&
            $this->getZip() == $address->getZip() &&
            $this->getTitle() == $address->getTitle() &&
            $this->isTitleCompany() == $address->isTitleCompany() &&
            $this->isTitleWoman() == $address->isTitleWoman() &&
            $this->isTitleMan() == $address->isTitleMan()
        );
    }

    /**
     * @return bool
     */
    public function isTitleMan()
    {
        return $this->getTitle() == self::TITLE_MAN;
    }

    /**
     * @return bool
     */
    public function isTitleWoman()
    {
        return $this->getTitle() == self::TITLE_WOMAN;
    }

    /**
     * @return bool
     */
    public function isTitleCompany()
    {
        return $this->getTitle() == self::TITLE_COMPANY;
    }

    /**
     * @param UserInterface $user
     * @param bool $stopPropagation
     * @return $this
     */
    public function setUser(UserInterface $user = null, $stopPropagation = false)
    {
        if(!$stopPropagation) {
            if(!is_null($this->user)) {
                $this->user->removeAddress($this, true);
            }
            if(!is_null($user)) {
                $user->addAddress($this, true);
            }
        }
        $this->user = $user;
        return $this;
    }
    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return AddressInterface
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return array
     */
    public static function getTitles()
    {
        return array(
            'TITLE_WOMAN' => self::TITLE_WOMAN,
            'TITLE_MAN' => self::TITLE_MAN,
            'TITLE_COMPANY' => self::TITLE_COMPANY
        );
    }
}