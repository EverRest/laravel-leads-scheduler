<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Repositories\PartnerRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class PartnerSeeder extends Seeder
{
    /**
     * @param PartnerRepository $partnerRepository
     */
    public function __construct(private readonly PartnerRepository $partnerRepository)
    {
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Config::get('partners') as $externalId => $slug)
        {
            $this->partnerRepository
                ->firstOrCreate([
                    'external_id' => $externalId,
                    'name' => ucwords($slug),
                    'code' => strtolower($slug),
                    'dto_redirect_key' => match ($externalId) {
                        1 => 'extras.redirect.url',
                        2 => 'data.redirect_url',
                        3 => 'auto_login_url',
                        default => throw new InvalidArgumentException('Invalid partner_id'),
                    },
                ]);
        }
    }
}
