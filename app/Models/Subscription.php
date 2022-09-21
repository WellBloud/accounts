<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stripe\Invoice;

/**
 * @property int $id
 * @property string $account_id
 * @property string $customer_id
 * @property string $subscription_id
 * @property string $status
 * @property Carbon|null $valid_to
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Subscription extends Model
{
    use HasFactory;
    use SoftDeletes;

    private const ACCOUNT_ID = 'account_id';

    protected $fillable = ['account_id', 'customer_id', 'subscription_id', 'status', 'valid_to'];

    protected $visible = ['customer_id', 'subscription_id', 'status', 'valid_to', 'active'];

    protected $appends = ['active'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, self::ACCOUNT_ID);
    }

    public function getActiveAttribute(): bool
    {
        return $this->status === Invoice::STATUS_PAID;
    }
}
