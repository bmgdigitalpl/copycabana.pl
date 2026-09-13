<?php

namespace App\Policies;

use App\Models\QuoteRequest;
use App\Models\User;

class QuoteRequestPolicy
{
    public function view(User $user, QuoteRequest $quoteRequest): bool
    {
        return $user->isCustomer() && $user->client_id === $quoteRequest->client_id;
    }
}
