<?php

namespace Ibrows\SyliusShopBundle\Model\Address;

use Ibrows\SyliusShopBundle\Model\User\UserInterface;

interface AddressInterface
{
    const
        TITLE_MAN = 'MAN',
        TITLE_WOMAN = 'WOMAN',
        TITLE_COMPANY = 'COMPANY'
    ;

    /**
     * @return bool
     */
    public function isTitleMan();

    /**
     * @return bool
     */
    public function isTitleWoman();

    /**
     * @return bool
     */
    public function isTitleCompany();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return int
     */
    public function getId();

    /**
     * @param string $title
     *
     * @return AddressInterface
     */
    public function setTitle($title);

    /**
     * @return array
     */
    public static function getTitles();

    /**
     * @param string $city
     *
     * @return AddressInterface
     */
    public function setCity($city);

    /**
     * @return string
     */
    public function getCity();

    /**
     * @param string $company
     *
     * @return AddressInterface
     */
    public function setCompany($company);

    /**
     * @return string
     */
    public function getCompany();

    /**
     * @param string $country
     *
     * @return AddressInterface
     */
    public function setCountry($country);

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @param string $email
     *
     * @return AddressInterface
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $firstname
     *
     * @return AddressInterface
     */
    public function setFirstname($firstname);

    /**
     * @return string
     */
    public function getFirstname();

    /**
     * @param string $lastname
     *
     * @return AddressInterface
     */
    public function setLastname($lastname);

    /**
     * @return string
     */
    public function getLastname();

    /**
     * @param string $phone
     *
     * @return AddressInterface
     */
    public function setPhone($phone);

    /**
     * @return string
     */
    public function getPhone();

    /**
     * @param string $street
     *
     * @return AddressInterface
     */
    public function setStreet($street);

    /**
     * @return string
     */
    public function getStreet();

    /**
     * @param string $zip
     *
     * @return AddressInterface
     */
    public function setZip($zip);

    /**
     * @return string
     */
    public function getZip();

    /**
     * @param AddressInterface $address
     *
     * @return bool
     */
    public function compare(AddressInterface $address);

    /**
     * @param UserInterface $user
     *
     * @return AddressInterface
     */
    public function setUser(UserInterface $user);

    /**
     * @return UserInterface
     */
    public function getUser();
}
