<?php
declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Str;

final class PasswordGenerator
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
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $rand = rand(0, strlen($possibleChars) - 1);
            $password .= substr($possibleChars, $rand, 1);
        }
        $password .= rand(1, 1000);
        $password .= strtoupper(Str::random(1));
        $password .= strtolower(Str::random(1));

        return $password;
    }
}
