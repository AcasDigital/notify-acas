<?php

namespace App\EmploymentDispute\Submission;

/**
 * WARNING: This code is replicated from a C# class that exists with W3P (managed by Tisski)
 * If the normalisation needs modifying the code in both locations must be modified.
 */
class PayloadNormaliser
{
    public const REGEX_PUNCTUATION = "/[.)(,;<>?\\\"\\]\\['#]/";
    public const REGEX_DASH = '/-/';
    public const REGEX_SLASH = '/[\\/\\\\]/';
    public const REGEX_SUFFIX = "/((and) co| co| ltd| plc| limited| in liquidation| in administration| t\/as| t\/a| a.k.a| group| grp| aka)/i";
    public const REGEX_AND = '/[&\\+]/';
    public const REGEX_DOUBLE_WHITESPACE = '/(  +)/';
    public const REGEX_WHITESPACE = '/\\s/';
    public const REGEX_SALUTATION = '/(mrs )|(mr )|(ms )|(miss )|(missus )/i';
    public const REGEX_APOSTROPHE = '/\'/i';
    public const REGEX_DIGITS = '/\\D/i';

    public function __construct(
    ) {
    }

    public function normaliseOrganisation(?string $text): string
    {
        if (is_null($text)) {
            return '';
        }

        $output = strtolower($text);
        $output = preg_replace(self::REGEX_APOSTROPHE, '', $output);
        $output = preg_replace(self::REGEX_PUNCTUATION, '', $output);
        $output = preg_replace(self::REGEX_DASH, ' ', $output);
        $output = preg_replace(self::REGEX_AND, 'and', $output);
        $output = preg_replace(self::REGEX_SUFFIX, '', $output);
        $output = preg_replace(self::REGEX_SLASH, ' ', $output);
        $output = preg_replace(self::REGEX_DOUBLE_WHITESPACE, ' ', $output);
        $output = trim($output);

        return $output;
    }

    public function normalisePostCode(?string $text): string
    {
        if (is_null($text)) {
            return '';
        }

        $output = strtolower($text);
        $output = preg_replace(self::REGEX_APOSTROPHE, '', $output);
        $output = preg_replace(self::REGEX_WHITESPACE, '', $output);
        $output = trim($output);

        return $output;
    }

    public function normaliseFirstName(?string $text)
    {
        if (is_null($text)) {
            return '';
        }

        $output = strtolower($text);
        $output = preg_replace(self::REGEX_APOSTROPHE, '', $output);
        $output = preg_replace(self::REGEX_SALUTATION, '', $output);

        return $output;
    }

    public function normalisePhone(?string $text): string
    {
        if (is_null($text)) {
            return '';
        }

        $output = strtolower($text);
        $output = preg_replace(self::REGEX_APOSTROPHE, '', $output);
        $output = preg_replace(self::REGEX_DIGITS, '', $output);

        return $output;
    }
}
