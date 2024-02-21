<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Repositories\PartnerRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

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
                ]);
        }
    }
}
