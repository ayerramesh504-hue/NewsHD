<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class NepseApi extends BaseController
{
    private const SYMBOLS = ['NABIL', 'NICA', 'NIFRA', 'NTC', 'HIDCL', 'SCB', 'EBL', 'GBIME', 'SHIVM', 'UPPER', 'CHCL', 'CIT'];

    public function index()
    {
        $client = service('curlrequest', ['timeout' => 6, 'connect_timeout' => 4, 'http_errors' => false]);
        $quotes = [];
        $errors = [];

        foreach (self::SYMBOLS as $symbol) {
            try {
                $response = $client->get('https://sharebazaar.vercel.app/api', ['query' => ['symbol' => $symbol]]);
                $payload = json_decode((string) $response->getBody(), true);
                if ($response->getStatusCode() >= 400 || ! is_array($payload)) {
                    $errors[] = $symbol;
                    continue;
                }
                $data = $payload['data'] ?? $payload;
                $quotes[] = [
                    'symbol'     => $symbol,
                    'company'    => $data['company_name'] ?? $data['companyName'] ?? $symbol,
                    'price'      => $this->number($data, ['price', 'ltp', 'lastPrice']),
                    'previous'   => $this->number($data, ['previousClose', 'previous_close', 'prevClose']),
                    'change'     => $this->number($data, ['change', 'difference']),
                    'percent'    => $this->number($data, ['changePercent', 'change_percentage', 'percentChange']),
                    'volume'     => $this->number($data, ['volume', 'totalTradedQuantity']),
                    'updated_at' => $data['updatedAt'] ?? $data['lastUpdated'] ?? $data['last_updated'] ?? date('c'),
                ];
            } catch (\Throwable $e) {
                $errors[] = $symbol;
            }
        }

        return $this->response->setJSON([
            'success' => ! empty($quotes),
            'quotes'  => $quotes,
            'errors'  => $errors,
            'source'  => 'ShareBazaar / public NEPSE data',
            'updated' => date('c'),
        ]);
    }

    private function number(array $data, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && is_numeric($data[$key])) return (float) $data[$key];
        }
        return null;
    }
}
