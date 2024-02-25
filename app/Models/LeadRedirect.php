<?php
declare(strict_types=1);

namespace App\Models;

use App\Traits\HasLeadRelation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadRedirect extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasLeadRelation;

    /**
     * @var string[] $fillable
     */
    protected $fillable = [
        'lead_id',
        'link',
        'file',
    ];

    /**
     * @var string[] $hidden
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
