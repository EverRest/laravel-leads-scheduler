<?php
declare(strict_types=1);

namespace App\Dto;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

final class AffiliateKingzDto extends Data
{
    /**
     * @param string $first_name
     * @param string $last_name
     * @param string $password
     * @param string $email
     * @param string $phone
     * @param string $area_code
     * @param string $ip
     * @param string $offer_name
     */
    public function __construct(
        #[MapInputName('first_name')]
        public string $first_name,
        #[MapInputName('last_name')]
        public string $last_name,
        #[MapInputName('password')]
        public string  $password,
        #[MapInputName('email')]
        public string  $email,
        #[MapInputName('phone')]
        public string $phone,
        #[MapInputName('phone_code')]
        public string $area_code,
        #[MapInputName('ip')]
        public string $ip,
        #[MapInputName('offer_name')]
        public string $offer_name,
    )
    {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'password' => $this->password,
            'email' => $this->email,
            'funnel' => $this->offer_name,
            'affid' => '22',
            'phone' => $this->phone,
            'area_code' => $this->area_code,
            'hitid' => '9e5b40ba27c04c4ffe073437cf1e3a5a',
            '_ip' => $this->ip,
        ];
    }
}
