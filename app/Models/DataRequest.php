<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'type', 'status', 'requested_at', 'completed_at', 'notes'])]
class DataRequest extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['requested_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
