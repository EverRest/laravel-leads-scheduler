<?php
declare(strict_types=1);

namespace App\Dto;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

final class AstroPortDto extends Data
{
    /**
     * @param string $external_id
     * @param string $country
     * @param string $host
     * @param string $port
     */
    public function __construct(
        #[MapInputName('id')]
        public string $external_id,
        #[MapInputName('country')]
        public string $country,
        #[MapInputName('node.ip')]
        public string $host,
        #[MapInputName('ports.http')]
        public string $port,
    )
    {
    }
}
