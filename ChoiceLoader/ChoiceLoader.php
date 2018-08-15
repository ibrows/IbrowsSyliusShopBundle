<?php
namespace Ibrows\SyliusShopBundle\ChoiceLoader;

use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class ChoiceLoader implements ChoiceLoaderInterface
{

    public $choices;

    /**
     * ChoiceLoader constructor.
     * @param $chocies
     */
    public function __construct($choices)
    {
        $this->choices = $choices;
    }


    public function loadChoiceList($value = null)
    {
        return new ArrayChoiceList(
            $this->choices,
            function ($val) {
                if(is_null($val)){
                    return null;
                }

                if (!is_string($val)) {
                    return $val->getServiceId();
                }

                return $val;
            });
    }

    public function loadChoicesForValues(array $values, $value = null)
    {
        $result = [ ];

        foreach ($values as $val)
        {
            $key = array_search($val, $this->choices, true);

            if ($key !== false)
                $result[ ] = $key;
        }

        return $result;
    }

    public function loadValuesForChoices(array $choices, $value = null)
    {
        $result = [ ];

        foreach ($choices as $label)
        {
            if (isset($this->choices[ $label ]))
                $result[ ] = $this->choices[ $label ];
        }

        return $result;
    }
}