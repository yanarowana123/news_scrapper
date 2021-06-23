<?php


namespace App\Services\Scrappers;


use App\Models\RequestLog;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use function PHPUnit\Framework\throwException;

abstract class ScrapperService
{

    protected Client $client;
    protected string $url;

    public function __construct(string $url)
    {
        $this->client = new Client();
        $this->url = $url;
    }


    protected function getContent(): string
    {
        try {
            $response = $this->client->get($this->url);
            $this->writeLogs($response->getStatusCode(),$response->getBody());
            return $this->client->get($this->url)->getBody()->getContents();
        } catch (Exception $exception) {
            throwException($exception);
        }
    }

    protected function writeLogs(int $code,  $body): void
    {
        RequestLog::create([
            'request_method' => 'GET',
            'request_url' => $this->url,
            'response_http_code' => $code,
            'response_body' => $body
        ]);
    }

    abstract public function scrape(): void;
}
