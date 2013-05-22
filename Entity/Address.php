<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Model\Address\AddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_address")
 */
class Address implements InvoiceAddressInterface, DeliveryAddressInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $firstname
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"sylius_wizard_address"})
     */
    protected $firstname;

    /**
     * @var string $lastname
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"sylius_wizard_address"})
     */
    protected $lastname;

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
     */
    protected $zip;

    /**
     * @var string $city
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"sylius_wizard_address"})
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
     */
    protected $email;

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
            $this->getZip() == $address->getZip()
        );
    }
}