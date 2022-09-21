<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $account_id
 * @property string $user_id
 * @property string $role_id
 */
class AccountUser extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const USER_ID = 'user_id';
    public const ACCOUNT_ID = 'account_id';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [self::USER_ID];

    protected $primaryKey = self::USER_ID;

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, self::ACCOUNT_ID);
    }
}
