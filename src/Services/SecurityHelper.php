<?php

namespace App\Services;

class SecurityHelper
{
    public static function generateUrlAccessHash(string $email, string $memorableWord, int $timestamp): string
    {
        return hash('sha256', "$email.$memorableWord.$timestamp");
    }
}
