<?php
declare(strict_types=1);

namespace App\Dto;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

final class CmAffsDto extends Data
{
    /**
     * @param string $first_name
     * @param string $last_name
     * @param string $password
     * @param string $email
     * @param string $phone
     * @param string $phone_code
     * @param string $ip
     * @param string $offer_name
     * @param string $offer_url
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
        public string $phone_code,
        #[MapInputName('ip')]
        public string $ip,
        #[MapInputName('offer_name')]
        public string $offer_name,
        #[MapInputName('offer_url')]
        public string $offer_url,
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
            'phone' => $this->phone_code . $this->phone,
            'custom1' => $this->offer_name,
            'custom5' => 'ba0bcb793a87f1c0dcb7cfccf55c8dee',
            'offer_name' => $this->offer_name,
            'offer_website' => $this->offer_url,
            'ip' => $this->ip,
        ];
    }
}
