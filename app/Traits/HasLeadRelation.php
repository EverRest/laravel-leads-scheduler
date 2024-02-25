<?php

namespace App\Traits;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasLeadRelation
{
    /**
     * @param $related
     * @param $foreignKey
     * @param $ownerKey
     * @param $relation
     *
     * @return mixed
     */
    abstract public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null);

    /**
     * @return BelongsTo
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
