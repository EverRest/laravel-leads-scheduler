<?php
declare(strict_types=1);

namespace App\Helpers;

class PasswordGenerator
{
    /**
     * @param int $length
     *
     * @return string
     */
    public static function generatePassword(int $length = 6): string
    {
        $possibleChars =
            "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz0123456789";
        $password = 'Aa1';
        for ($i = 0; $i < $length; $i++) {
            $rand = rand(0, strlen($possibleChars) - 1);
            $password .= substr($possibleChars, $rand, 1);
        }
        $password .= rand(1, 1000);
        return $password;
    }
}
