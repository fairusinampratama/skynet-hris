<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $baseUrl;
    protected $apiKey;
    protected $device;

    public function __construct()
    {
        $this->baseUrl = config('services.whatspie.base_url', 'https://api.whatspie.com'); // Default
        $this->apiKey = config('services.whatspie.api_key');
        $this->device = config('services.whatspie.device');
    }

    /**
     * Send a text message.
     */
    public function sendMessage($receiver, $message)
    {
        if (!$this->apiKey || !$this->device) {
            Log::warning("Whatspie credentials not configured.");
            return false;
        }

        // Standardizing phone number (e.g., removing +, ensuring 62 prefix if needed)
        // For now assuming clean input or simple specific formatting
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}/messages", [
            'device' => $this->device,
            'receiver' => $receiver,
            'type' => 'chat',
            'message' => $message,
        ]);

        if ($response->failed()) {
            Log::error("Failed to send WhatsApp message to $receiver: " . $response->body());
            return false;
        }

        return true;
    }

    /**
     * Send a document/file.
     */
    public function sendDocument($receiver, $fileUrl, $caption = '') // API likely expects a public URL or base64
    {
        if (!$this->apiKey || !$this->device) {
            Log::warning("Whatspie credentials not configured.");
            return false;
        }
        
        // Note: Check Whatspie documentation for specific file upload requirements.
        // Often APIs require a public URL for the file or multipart upload.
        // Assuming public URL for this implementation.

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}/messages", [
            'device' => $this->device,
            'receiver' => $receiver,
            'type' => 'file',
            'file_url' => $fileUrl,
            'message' => $caption, // Caption often mapped to 'message'
        ]);

        if ($response->failed()) {
             Log::error("Failed to send WhatsApp document to $receiver: " . $response->body());
             return false;
        }

        return true;
    }
}
