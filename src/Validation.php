<?php

namespace Transitive\Utils;

abstract class Validation
{
    private static $formValidation;
    private static $formValidity;

    public static function validateForm(array $formElements, array $values): bool
    {
        self::$formValidation = array();
        self::$formValidity = true;

        if(isset($formElements)) {
            foreach($formElements as $name => $element)
                if(isset($values[$name]) && (gettype($element) == 'object' && get_class($element) == 'Closure') && (self::$formValidation[$name] = $element($values[$name])) !== true) {
                    self::$formValidation[$name] = '<p class="error">'.self::$formValidation[$name].'</p>';
                    self::$formValidity = false;
                }
        }

        return self::isFormValid();
    }

    public static function trimForm(array $formElements, array &$values) 
    {
        if(isset($formElements)) {
            foreach($formElements as $elementName)
                if(isset($values[$elementName]))
                    $values[$elementName] = trim($values[$elementName]);
        }
    }

    public static function isFormValid(): bool
    {
        return self::$formValidity;
    }

    public static function isValid(string $formElementName) {
        return (isset(self::$formValidation[$formElementName])) ? self::$formValidation[$formElementName] : null;
    }

    public static function invalidMessage(string $formElementName)
    {
        return (!empty($formElementName) && ($message = self::isValid($formElementName)) !== true) ? $message : '';
    }

    public static function is_valid_phoneNumber(string $number): bool
    {
        return (!preg_match('/^([+]?\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i', preg_replace('/ /', '', $number))) ? false : true;
    }

    public static function is_valid_email(string $str): bool
    {
        return (!preg_match('/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', $str)) ? false : true;
    }

    public static function contains_numeric(string $str): bool
    {
        return preg_match('/[0-9]+/', $str);
    }

    public static function contains(string $needles, string $str): bool
    {
        return strlen($str) != strcspn($str, $needles);
    }

    public static function format_date(string $str): bool
    {
        if(strtotime($str) !== false)
            return date('Y-m-d', strtotime($str));

        return false;
    }

    public static function is_within($number, $low, $high): bool
    {
        return $number > $low && $number <= $high;
    }

    public static function is_port_number($number): bool
    {
        return self::is_within($number, 0, 65535);
    }

    public static function is_valid_SQL_date(string $date): bool
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches))
            if (checkdate($matches[2], $matches[3], $matches[1]))
                return true;

        return false;
    }
}
