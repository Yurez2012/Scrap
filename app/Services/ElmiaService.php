<?php

namespace App\Services;

use GuzzleHttp\Client;

class ElmiaService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://www.elmia.se/api/v1/',
            'timeout'  => 10.0,
        ]);
    }

    /**
     * Отримати компанії за projectId
     *
     * @param int|string $projectId
     * @return array
     */
    public function getCompanies($projectId): array
    {
        $response = $this->client->get('companies', [
            'query' => [
                'projectIds' => $projectId
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data ?? [];
    }

    /**
     * Отримати деталі стенду компанії
     *
     * @param int|string $exhibitorId
     * @param int|string $standId
     * @param string $lang
     * @param bool $includeProducts
     * @return array
     */
    public function getExhibitorDetails($exhibitorId, $standId, $lang = 'en', $includeProducts = true): array
    {
        $response = $this->client->get("catalog/exhibitor/{$exhibitorId}/{$standId}", [
            'query' => [
                'lang' => $lang,
                'includeProducts' => $includeProducts ? 'true' : 'false',
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
