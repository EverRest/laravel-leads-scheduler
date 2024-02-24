<?php
declare(strict_types=1);

namespace App\Models;

use App\Helpers\PasswordGenerator;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Lead
 * @package App\Models
 */
class Lead extends Model
{
    use HasFactory;

    /**
     * @var string[] $fillable
     */
    protected $fillable = [
        'import',
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_code',
        'password',
        'country',
        'partner_id',
        'is_sent',
        'scheduled_at',
    ];

    /**
     * @var string[] $hidden
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * @var string[] $casts
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_sent' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function Partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * @return HasOne
     */
    public function leadProxy(): HasOne
    {
        return $this->hasOne(LeadProxy::class);
    }

    /**
     * @return HasOne
     */
    public function result(): HasOne
    {
        return $this->hasOne(LeadResult::class);
    }

    /**
     * @return Attribute
     */
    public function password(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if(is_null($value)) {
                    return PasswordGenerator::generatePassword();
                }
                return $value;
            },
        );
    }
}
