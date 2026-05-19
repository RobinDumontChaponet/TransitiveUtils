<?php

namespace Transitive\Utils;

use DateTimeImmutable;

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
                if(isset($values[$name]) && ('object' == gettype($element) && 'Closure' == get_class($element)) && (self::$formValidation[$name] = $element($values[$name])) !== true) {
                    self::$formValidation[$name] = '<p class="error">'.self::$formValidation[$name].'</p>';
                    self::$formValidity = false;
                }
        }

        return self::isFormValid();
    }

    public static function trimForm(array $formElements, array &$values): void
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

    public static function invalidMessage(string $formElementName): ?string
    {
        return (!empty($formElementName) && true !== ($message = self::isValid($formElementName))) ? $message : '';
    }

    public static function is_valid_phoneNumber(string $number): bool
    {
        return (!preg_match('/^([+]?\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i', preg_replace('/ /', '', $number))) ? false : true;
    }

    public static function is_valid_email(string $str): bool
    {
        return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function contains_numeric(string $str): bool
    {
        return preg_match('/[0-9]+/', $str) === 1;
    }

    public static function contains(string $needles, string $str): bool
    {
        return strlen($str) != strcspn($str, $needles);
    }

    public static function format_date(string $str): string|false
    {
        $timestamp = strtotime($str);
        if(false !== $timestamp)
            return date('Y-m-d', $timestamp);

        return false;
    }

    public static function is_within($number, $low, $high): bool
    {
        $number = filter_var($number, FILTER_VALIDATE_FLOAT);
        $low = filter_var($low, FILTER_VALIDATE_FLOAT);
        $high = filter_var($high, FILTER_VALIDATE_FLOAT);

        if($number === false || $low === false || $high === false)
            return false;

        return $number > $low && $number <= $high;
    }

    public static function is_port_number($number): bool
    {
        return filter_var(
            $number,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 65535]]
        ) !== false;
    }

    public static function is_valid_SQL_date(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();

        return $parsed !== false
            && $parsed->format('Y-m-d') === $date
            && (!$errors || ($errors['warning_count'] === 0 && $errors['error_count'] === 0));
    }
}
