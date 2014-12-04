<?php

/**
 * Created by PhpStorm.
 * Project: coffeeconnection
 *
 * User: mikemeier
 * Date: 04.12.14
 * Time: 17:33
 */

namespace Ibrows\SyliusShopBundle\CountryCode;

class CountryCode
{
    /**
     * @var array
     */
    public static $data = array('alpha2' => array(), 'alpha3' => array());

    /**
     * @var bool
     */
    protected static $initialized = false;

    /**
     * @param string $alpha2
     * @return string
     */
    public static function getAlpha3FromAlpha2($alpha2)
    {
        $data = self::getData();
        return isset($data['alpha2'][$alpha2]) ? $data['alpha2'][$alpha2] : null;
    }

    /**
     * @param string $alpha3
     * @return string
     */
    public static function getAlpha2FromAlpha3($alpha3)
    {
        $data = self::getData();
        return isset($data['alpha3'][$alpha3]) ? $data['alpha3'][$alpha3] : null;
    }

    /**
     * @throws \Exception
     */
    protected static function getData()
    {
        if (self::$initialized) {
            return self::$data;
        }

        if (($handle = fopen(__DIR__ . '/country_codes.csv', 'r')) !== false) {

            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                self::$data['alpha2'][$data[1]] = $data[2];
                self::$data['alpha3'][$data[2]] = $data[1];
            }

            fclose($handle);

            self::$initialized = true;
            return self::$data;
        }

        throw new \Exception("Could not open data");
    }
}