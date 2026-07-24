<?php

namespace App\Events;

use App\Models\ApiKey;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiKeyLimitExceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(public ApiKey $apiKey, public string $limitType) {} // 'rate_limit' or 'quota'
}
