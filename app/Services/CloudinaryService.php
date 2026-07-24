<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    protected ?string $cloudName = null;
    protected ?string $apiKey = null;
    protected ?string $apiSecret = null;
    protected bool $configured = false;

    public function __construct()
    {
        $url = env('CLOUDINARY_URL');
        if (! empty($url)) {
            $parsed = parse_url($url);
            if (isset($parsed['host'], $parsed['user'], $parsed['pass'])) {
                $this->cloudName = $parsed['host'];
                $this->apiKey = $parsed['user'];
                $this->apiSecret = $parsed['pass'];
                $this->configured = true;
            }
        }
    }

    public function isConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * Upload a raw file to Cloudinary.
     *
     * @param string $localFilePath Absolute path to local file
     * @param string $folder Folder prefix in Cloudinary
     * @return array|null Returns ['url' => ..., 'public_id' => ...] or null on failure
     */
    public function uploadRaw(string $localFilePath, string $folder = 'backups'): ?array
    {
        if (! $this->isConfigured() || ! file_exists($localFilePath)) {
            return null;
        }

        try {
            $fileName = basename($localFilePath);
            $filenameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
            $publicId = $folder . '/' . $filenameWithoutExt;
            $timestamp = time();

            // Cloudinary signature parameter string must be sorted alphabetically
            $stringToSign = "public_id={$publicId}&timestamp={$timestamp}{$this->apiSecret}";
            $signature = sha1($stringToSign);

            $response = Http::attach(
                'file',
                file_get_contents($localFilePath),
                $fileName
            )->post("https://api.cloudinary.com/v1_1/{$this->cloudName}/raw/upload", [
                'api_key' => $this->apiKey,
                'timestamp' => $timestamp,
                'public_id' => $publicId,
                'signature' => $signature,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                return [
                    'url' => $json['secure_url'] ?? $json['url'] ?? null,
                    'public_id' => $json['public_id'] ?? $publicId,
                ];
            }

            Log::error('Cloudinary raw upload failed: ' . $response->body());
            return null;
        } catch (Exception $e) {
            Log::error('Cloudinary upload exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a raw asset from Cloudinary.
     */
    public function deleteRaw(string $publicId): bool
    {
        if (! $this->isConfigured() || empty($publicId)) {
            return false;
        }

        try {
            $timestamp = time();
            $stringToSign = "public_id={$publicId}&timestamp={$timestamp}{$this->apiSecret}";
            $signature = sha1($stringToSign);

            $response = Http::post("https://api.cloudinary.com/v1_1/{$this->cloudName}/raw/destroy", [
                'api_key' => $this->apiKey,
                'timestamp' => $timestamp,
                'public_id' => $publicId,
                'signature' => $signature,
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Cloudinary delete exception: ' . $e->getMessage());
            return false;
        }
    }
}
