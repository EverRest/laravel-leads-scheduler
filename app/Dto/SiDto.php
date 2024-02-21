<?php
declare(strict_types=1);

namespace App\Dto;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class SiDto extends Data
{
    /**
     * @param string $first_name
     * @param string $last_name
     * @param float $password
     * @param float $email
     * @param string $phone
     * @param string $phone_code
     */
    public function __construct(
        #[MapInputName('first_name')]
        public string $first_name,
        #[MapInputName('last_name')]
        public string $last_name,
        #[MapInputName('password')]
        public float  $password,
        #[MapInputName('email')]
        public float  $email,
        #[MapInputName('phone')]
        public string $phone,
        #[MapInputName('phone_code')]
        public string $phone_code
    )
    {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'profile' => [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'password' => $this->password,
                'email' => $this->email,
                'phone' => $this->phone_code . $this->phone,
            ],
            'tp_source' => 'Quantum BitQZ 3480',
            'tp_aff_sub' => '',
            'tp_aff_sub2' => '384jnc7fob1p',
            'tp_aff_sub9' => '2870',
            'tp_aff_sub5' => '449',
            'tp_aff_sub4' => 'Quantum BitQZ 3480',
        ];
    }
}
