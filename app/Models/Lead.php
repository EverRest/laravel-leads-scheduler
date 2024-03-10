<?php
declare(strict_types=1);

namespace App\Models;

use App\Helpers\PasswordGenerator;
use App\Services\Partner\AffiliateKingzService;
use App\Services\Partner\CmAffsService;
use App\Services\Partner\StarkIrevService;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\RuntimeException;

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
        'scheduled_at',
    ];

    /**
     * @var string[] $with
     */
    protected $with = [
        'leadProxy',
        'leadRedirect',
        'leadResult',
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
    public function leadRedirect(): HasOne
    {
        return $this->hasOne(LeadRedirect::class);
    }

    /**
     * @return HasOne
     */
    public function leadResult(): HasOne
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

    /**
     * @return Attribute
     */
//    public function redirectLinkKey(): Attribute
//    {
//        return Attribute::make(
//            get: function () {
//                if($this->leadResult) {
//                    return match ($this->partner->external_id) {
//                        '1' => 'data.extras.redirect.url',
//                        '2' => 'data.data.redirect_url',
//                        '3' => 'data.auto_login_url',
//                        default => throw new InvalidArgumentException('Invalid partner_id'),
//                    };
//                }
//                throw new Exception('Lead result not found.');
//            },
//        );
//    }
}
