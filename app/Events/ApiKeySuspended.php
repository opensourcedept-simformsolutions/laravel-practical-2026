<?php

namespace App\Events;

use App\Models\ApiKey;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiKeySuspended
{
    use Dispatchable, SerializesModels;

    public function __construct(public ApiKey $apiKey) {}
}
