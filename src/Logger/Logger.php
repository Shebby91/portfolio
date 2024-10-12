<?php
namespace App\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

class Logger implements LoggerInterface
{
    use LoggerTrait;

    private $httpClient;
    private $logstashUrl;

    public function __construct(string $logstashUrl)
    {
        $this->httpClient = new \GuzzleHttp\Client();
        $this->logstashUrl = $logstashUrl;
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $payload = json_encode(array_merge(['level' => $level, 'message' => $message], $context));
        
        try {
            $this->httpClient->post($this->logstashUrl, [
                'body' => $payload,
                'headers' => ['Content-Type' => 'application/json'],
            ]);
        } catch (\Exception $e) {
            // Handle the exception (e.g. log it to a file)
        }
    }
}