<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Account extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const NAME = 'name';
    public const ACCOUNT_ID = 'account_id';

    protected $fillable = [self::NAME];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $with = ['information', 'subscriptions'];

    public function users(): HasMany
    {
        return $this->hasMany(AccountUser::class, self::ACCOUNT_ID);
    }

    public function information(): MorphOne
    {
        return $this->morphOne(Information::class, 'informationable');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, self::ACCOUNT_ID);
    }
}
