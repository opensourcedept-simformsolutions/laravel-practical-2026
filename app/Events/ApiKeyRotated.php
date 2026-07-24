<?php

namespace App\Events;

use App\Models\ApiKey;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiKeyRotated
{
    use Dispatchable, SerializesModels;

    public function __construct(public ApiKey $oldApiKey, public ApiKey $newApiKey) {}
}
