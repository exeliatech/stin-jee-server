<?php namespace App;

abstract class Types extends Enum
{
    const InStore = 1;
    const Coupon = 2;
    const Delivery = 3;
    const HomeFood = 4;
}

abstract class Actions extends Enum
{
    const Order = 1;
    const OrderNow = 2;
    const OrderDelivery = 3;
    const ClickHere = 4;
    const MoreInfo = 5;
    const Proceed = 6;
    const BuyCoupon = 7;
    const GetOffer = 8;
    const BuyNow = 9;
    const Website = 10;
}

abstract class Sources extends Enum
{
    const StinJee = 1;
    const HungryHouse = 2;
}

abstract class Enum
{
    private static $constCacheArray = NULL;

    private static function getConstants()
    {
        if (self::$constCacheArray == NULL) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new \ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function GeyKeys()
    {
        return array_keys(self::getConstants());
    }

    public static function isValidName($name, $strict = false)
    {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value, $strict = true)
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }

    public static function fromString($name)
    {
        if (self::isValidName($name, $strict = true)) {
            $constants = self::getConstants();
            return $constants[$name];
        }

        return false;
    }

    public static function toString($value)
    {
        if (self::isValidValue($value, $strict = true)) {
            return array_search($value, self::getConstants());
        }

        return false;
    }
}