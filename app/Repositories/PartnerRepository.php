<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Model;

final class PartnerRepository extends Repository
{
    /**
     * @var string $model
     */
    protected string $model = Partner::class;

    /**
     * Find a partner by external id
     *
     * @param string|int $externalId
     *
     * @return Model|null
     */
    public function findByExternalId(string|int $externalId): ?Model
    {
        return $this->query()->where('external_id', $externalId)->first();
    }
}
