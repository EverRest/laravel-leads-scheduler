<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadResult extends Model
{
    use HasFactory;

    /**
     * @var string[] $fillable
     */
    protected $fillable = [
        'lead_id',
        'data',
        'status',
        'screen_shot',
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
        'data' => 'json',
        'status' => 'integer',
    ];
}
