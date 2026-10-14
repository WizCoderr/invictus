<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    public const TYPE_MEMBER = 'member';

    public const TYPE_VISITOR = 'visitor';

    public const PAYMENT_CREATED = 'created';

    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'reference',
        'type',
        'name',
        'business_category_id',
        'chapter_id',
        'phone',
        'email',
        'invited_by',
        'amount',
        'gst_percent',
        'gst_amount',
        'total_amount',
        'razorpay_order_id',
        'razorpay_payment_id',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gst_percent' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function businessCategory(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /** Today's fee for a member or visitor: Admin > Settings, else config/registration.php, plus GST. */
    public static function pricing(string $type): array
    {
        $amount = round((float) Setting::get("{$type}_fee", config("registration.{$type}_fee")), 2);
        $gstPercent = (float) Setting::get('gst_percent', config('registration.gst_percent'));
        $gstAmount = round($amount * $gstPercent / 100, 2);

        return [
            'amount' => $amount,
            'gst_percent' => $gstPercent,
            'gst_amount' => $gstAmount,
            'total_amount' => round($amount + $gstAmount, 2),
        ];
    }
}
