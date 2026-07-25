<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /**
     * SALON-OPS-ENHANCEMENTS.md, "Customers" (Tier 3) — fixed tag set
     * rather than a free-text field or a full tags table. A salon's tag
     * vocabulary (VIP, bridal, allergy-sensitive, ...) is small and
     * doesn't need admin-managed CRUD; validated against this list in
     * CustomerController so `tags` never drifts into free text.
     */
    public const TAGS = [
        'vip' => 'VIP',
        'bridal' => 'Bridal',
        'regular' => 'Regular',
        'allergy_sensitive' => 'Allergy Sensitive',
    ];

    protected $fillable = [
        'name',
        'phone',
        'email',
        'notes',
        'tags',
        'date_of_birth',
        'anniversary_date',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'date_of_birth' => 'date',
            'anniversary_date' => 'date',
            'points_balance' => 'integer',
            'last_birthday_reminder_sent_at' => 'date',
            'last_anniversary_reminder_sent_at' => 'date',
        ];
    }

    public function jobs()
    {
        return $this->hasMany(SalonJob::class, 'customer_id');
    }

    public function pointsLedger()
    {
        return $this->hasMany(CustomerPoint::class)->latest('id');
    }

    public function totalSpent(): int
    {
        return (int) $this->jobs()->sum('total_paid');
    }

    public function visitCount(): int
    {
        return $this->jobs()->count();
    }

    public function lastVisit(): ?string
    {
        return $this->jobs()->max('job_date');
    }

    /**
     * Every customer with a birthday today who hasn't already had this
     * year's reminder sent — the query
     * `Console\Commands\SendCustomerOccasionReminders` runs daily.
     */
    public function scopeBirthdayToday($query)
    {
        return $query->whereNotNull('date_of_birth')
            ->whereNotNull('email')
            ->whereMonth('date_of_birth', now()->month)
            ->whereDay('date_of_birth', now()->day)
            ->where(function ($q) {
                $q->whereNull('last_birthday_reminder_sent_at')
                    ->orWhereYear('last_birthday_reminder_sent_at', '<', now()->year);
            });
    }

    public function scopeAnniversaryToday($query)
    {
        return $query->whereNotNull('anniversary_date')
            ->whereNotNull('email')
            ->whereMonth('anniversary_date', now()->month)
            ->whereDay('anniversary_date', now()->day)
            ->where(function ($q) {
                $q->whereNull('last_anniversary_reminder_sent_at')
                    ->orWhereYear('last_anniversary_reminder_sent_at', '<', now()->year);
            });
    }

    /**
     * Automatic earn — called from JobController::addPayment() for the
     * amount just paid. Silently does nothing for a non-positive amount
     * (callers pass a floored points-per-currency-unit conversion) rather
     * than erroring, since "0 points earned" on a small payment is an
     * expected, not exceptional, outcome.
     */
    public function earnPoints(int $points, ?string $reason = null, ?string $referenceType = null, ?int $referenceId = null, ?int $createdBy = null): ?CustomerPoint
    {
        if ($points <= 0) {
            return null;
        }

        return $this->applyPoints('earned', $points, $reason, $referenceType, $referenceId, $createdBy);
    }

    /**
     * Admin-triggered redemption. Caller (CustomerPointController) is
     * responsible for checking the balance covers it first — a redemption
     * for more than the customer has is a mistake to reject up front, not
     * a balance to quietly let go negative.
     */
    public function redeemPoints(int $points, ?string $reason = null, ?int $createdBy = null): CustomerPoint
    {
        return $this->applyPoints('redeemed', -abs($points), $reason, null, null, $createdBy);
    }

    /**
     * Manual correction, either direction — goodwill bonus, or fixing a
     * mistaken earn/redeem entry.
     */
    public function adjustPoints(int $delta, ?string $reason = null, ?int $createdBy = null): CustomerPoint
    {
        return $this->applyPoints('adjustment', $delta, $reason, null, null, $createdBy);
    }

    private function applyPoints(
        string $type,
        int $delta,
        ?string $reason,
        ?string $referenceType,
        ?int $referenceId,
        ?int $createdBy,
    ): CustomerPoint {
        $this->points_balance = $this->points_balance + $delta;
        $this->save();

        return $this->pointsLedger()->create([
            'type' => $type,
            'points' => $delta,
            'balance_after' => $this->points_balance,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_by' => $createdBy,
        ]);
    }
}
