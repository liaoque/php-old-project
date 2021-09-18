<?php


class Helper_Utils
{
    /**
     * @internal
     */
    public static function getClass($object)
    {
        $class = \get_class($object);

        return 'c' === $class[0] && 0 === strpos($class, "class@anonymous\0") ? get_parent_class($class) . '@anonymous' : $class;
    }


    public static function phoneMosaic($phone)
    {
        $value = preg_replace('/(\d{3})\d{5}(\d{3})/', '$1***$2', $phone);
        return $value;
    }
    public static function phoneLast($phone)
    {
        $value = preg_replace('/(\d{7})/', '', $phone);
        return $value;
    }



}
