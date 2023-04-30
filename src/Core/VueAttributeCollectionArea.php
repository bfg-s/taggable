<?php

namespace Lar\Tagable\Core;

use Illuminate\Contracts\Support\Arrayable;
use Lar\Layout\Respond;
use Lar\Tagable\Core\Extension\Tagable;

class VueAttributeCollectionArea extends AttributeCollectionArea implements Tagable
{
    /**
     * @param $attribute
     * @param string $value
     * @return AttributeCollectionArea
     * @throws \Exception
     */
    public function add($attribute, $value = ''): AttributeCollectionArea
    {
        return parent::add(...static::filterValue($attribute, $value));
    }

    /**
     * @param array $values
     * @return array
     */
    public static function filterArrayValues(array $values)
    {
        foreach ($values as $key => $value) {
            if (! is_numeric($key)) {
                unset($values[$key]);

                list($key, $value) = static::filterValue($key, $value);

                $values[$key] = $value;
            }
        }

        return $values;
    }

    /**
     * @param $attribute
     * @param $value
     * @return array
     */
    public static function filterValue($attribute, $value)
    {
        if ($value === true || $value === 'true') {
            $value = 'true';
            $attribute = ":$attribute";
        } elseif ($value === false || $value === 'false') {
            $value = 'false';
            $attribute = ":$attribute";
        } elseif (is_array($value) && ! (isset($value['exec']) && $value['exec'] instanceof Respond)) {
            $value = json_encode($value);
            $attribute = ":$attribute";
        } elseif ($value instanceof Arrayable) {
            $value = json_encode($value->toArray());
            $attribute = ":$attribute";
        }

        return [
            $attribute,
            $value,
        ];
    }
}
