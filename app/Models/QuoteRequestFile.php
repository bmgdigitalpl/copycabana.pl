<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'quote_request_id',
    'quote_request_item_id',
    'token_hash',
    'disk',
    'path',
    'original_name',
    'mime_type',
    'size',
    'sha256',
    'pages',
    'status',
    'expires_at',
])]
class QuoteRequestFile extends Model
{
    protected $hidden = ['token_hash'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<QuoteRequest, $this> */
    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    /** @return BelongsTo<QuoteRequestItem, $this> */
    public function quoteRequestItem(): BelongsTo
    {
        return $this->belongsTo(QuoteRequestItem::class);
    }
}
