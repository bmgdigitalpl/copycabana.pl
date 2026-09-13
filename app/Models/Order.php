<?php

namespace App\Models;

use App\Enums\Carrier;
use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

#[Fillable([
    'number',
    'success_token_hash',
    'idempotency_key',
    'idempotency_fingerprint',
    'client_id',
    'status',
    'payment_status',
    'currency',
    'customer_name',
    'customer_email',
    'customer_phone',
    'customer_company',
    'billing_address',
    'shipping_method',
    'carrier',
    'tracking_number',
    'shipping_address',
    'requested_by_date',
    'subtotal',
    'shipping_total',
    'tax_rate',
    'invoice_required',
    'invoice_nip',
    'total',
    'notes',
    'paid_at',
    'delivered_at',
])]
class Order extends Model
{
    protected $hidden = ['success_token_hash', 'idempotency_key', 'idempotency_fingerprint'];

    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'billing_address' => 'array',
            'shipping_address' => 'array',
            'requested_by_date' => 'date',
            'status' => OrderStatus::class,
            'carrier' => Carrier::class,
            'invoice_required' => 'boolean',
            'tax_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderFile, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(OrderFile::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return HasMany<OrderStatusHistory, $this> */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /** @return HasOne<Invoice, $this> */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function transitionTo(OrderStatus $status, ?string $note = null): void
    {
        $current = $this->status instanceof OrderStatus ? $this->status : OrderStatus::from((string) $this->status);

        if ($current === $status) {
            return;
        }

        if (! $current->canTransitionTo($status)) {
            throw new \DomainException("Cannot transition order from {$current->value} to {$status->value}.");
        }

        $this->forceFill([
            'status' => $status,
            'delivered_at' => $status === OrderStatus::Delivered ? now() : $this->delivered_at,
        ])->save();

        $this->statusHistories()->create([
            'from_status' => $current->value,
            'to_status' => $status->value,
            'note' => $note,
            'changed_by' => Auth::id(),
        ]);
    }

    public function prepareForPaymentRetry(): void
    {
        $current = $this->status instanceof OrderStatus ? $this->status : OrderStatus::from((string) $this->status);

        if (! in_array($current, [OrderStatus::Pending, OrderStatus::Cancelled], true)) {
            throw new \DomainException("Cannot retry payment for order in {$current->value} status.");
        }

        if ($current === OrderStatus::Cancelled) {
            $this->forceFill([
                'status' => OrderStatus::Pending,
                'payment_status' => 'pending',
            ])->save();
            $this->statusHistories()->create([
                'from_status' => OrderStatus::Cancelled,
                'to_status' => OrderStatus::Pending,
                'note' => 'Przygotowano ponowną próbę płatności.',
                'changed_by' => null,
            ]);

            return;
        }

        if ($this->payment_status !== 'pending') {
            $this->forceFill(['payment_status' => 'pending'])->save();
        }
    }

    public function trackingUrl(): ?string
    {
        return $this->carrier?->trackingUrl($this->tracking_number);
    }
}
