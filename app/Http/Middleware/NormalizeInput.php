<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeInput
{
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $request->merge(
            $this->normalize($request->all())
        );

        return $next($request);
    }

    private function normalize(array $data): array
    {
        foreach ($data as $key => $value) {

            if (in_array($key, $this->except, true)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->normalize($value);
                continue;
            }

            if (is_string($value)) {

                // Trim leading/trailing whitespace
                $value = trim($value);

                // Collapse multiple spaces/tabs/newlines into a single space
                $value = preg_replace('/\s+/u', ' ', $value);

                $data[$key] = $value;
            }
        }

        return $data;
    }
}
