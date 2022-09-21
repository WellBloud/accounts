<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $informationable_id
 * @property string $informationable_type
 * @property array $value JSON field in DB
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Information extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const COMPANY_NAME = 'company_name';
    public const ONBOARDING_STEP = 'onboarding_step';

    protected $fillable = ['value'];

    protected $visible = ['id', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    public function informationable(): MorphTo
    {
        return $this->morphTo();
    }
}
