<?php

namespace App\Libraries;

use App\Config\WhatsApp as WhatsAppConfig;
use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class WhatsAppService
{
    protected $config;
    protected $http;

    public function __construct($config = null)
    {
        $this->config = $config ?: new WhatsAppConfig();
        $this->http = Services::curlrequest();
    }

    public function isEnabled()
    {
        return !empty($this->config->enabled) && !empty($this->config->accessToken) && !empty($this->config->phoneNumberId);
    }

    /**
     * Normalize a phone number by adding default country code if missing leading +.
     */
    protected function normalizePhone($phone)
    {
        $phone = preg_replace('/\D+/', '', $phone); // keep digits only
        if ($this->config->defaultCountryCode && strpos($phone, $this->config->defaultCountryCode) !== 0) {
            // If number doesn't start with country code and also isn't starting with 0 then prepend
            if (substr($phone, 0, 1) !== '0') {
                $phone = $this->config->defaultCountryCode . $phone;
            }
        }
        return $phone;
    }

    /**
     * Send a PDF (or any document) by public URL using WhatsApp Cloud API.
     *
     * @param string $to E.164 number without + (e.g., 921234567890)
     * @param string $url Publicly accessible URL to the file
     * @param string $filename Suggested file name for the document
     * @param string|null $caption Optional caption text
     * @return array [success=>bool, status|error=>string, response=>mixed]
     */
    public function sendDocumentByUrl($to, $url, $filename, $caption = null)
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'WhatsApp not configured'];
        }

        $to = $this->normalizePhone($to);

        $endpoint = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            $this->config->graphApiVersion,
            $this->config->phoneNumberId
        );

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'document',
            'document' => [
                'link' => $url,
                'filename' => $filename,
            ],
        ];
        if ($caption) {
            $payload['document']['caption'] = $caption;
        }

        try {
            $response = $this->http->setHeader('Authorization', 'Bearer ' . $this->config->accessToken)
                ->setHeader('Content-Type', 'application/json')
                ->post($endpoint, json_encode($payload));

            $status = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($status >= 200 && $status < 300) {
                return ['success' => true, 'status' => $status, 'response' => $body];
            }
            return ['success' => false, 'status' => $status, 'error' => $body['error']['message'] ?? 'Unknown error', 'response' => $body];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
