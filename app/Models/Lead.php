<?php
declare(strict_types=1);

namespace App\Models;

use App\Helpers\PasswordGenerator;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'password',
        'country',
        'partner_id',
        'protocol',
        'scheduled_at',
        'country_name',
        'ip',
        'proxy_external_id',
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

    /**
     * @return Attribute
     */
    public function redirectLinkKey(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->partner->external_id) {
                    '1' => 'extras.redirect.url',
                    '2' => 'data.redirect_url',
                    '3' => 'auto_login_url',
                    default => throw new InvalidArgumentException('Invalid partner_id'),
                };
            },
        );
    }
}
