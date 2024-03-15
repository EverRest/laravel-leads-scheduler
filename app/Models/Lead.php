<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\PasswordGenerator;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;

/**
 * Class Lead
 * @package App\Models
 */
class Lead extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var string[] $fillable
     */
    protected $fillable = [
        'import',
        'first_name',
        'last_name',
        'is_sent',
        'email',
        'phone',
        'phone_code',
        'offer_name',
        'password',
        'country',
        'country_name',
        'partner_id',
        'protocol',
        'scheduled_at',
        'country_name',
        'ip',
        'proxy_external_id',
        'external_id',
        'traffic_source',
        'offer_url',
        'host',
        'port',
        'link',
        'file',
        'status',
        'data',
    ];

    /**
     * @var string[] $with
     */
    protected $with = [
        'partner',
    ];

    /**
     * @var string[] $hidden
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @var string[] $casts
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_sent' => 'boolean',
        'data' => 'json',
    ];

    /**
     * @return BelongsTo
     */
    public function Partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
