<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
     * @return BelongsTo
     */
    public function Partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * @return Attribute
     */
    public function password(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if(is_null($value)) {
                    return Str::random(10);
                }
                return $value;
            },
        );
    }
}
