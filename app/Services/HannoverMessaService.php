<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;

class HannoverMessaService
{
    protected Client $client;
    protected array $config;

    public function __construct()
    {
        $this->config = config('services.hannover_messa');

        $this->client = new Client([
            'base_uri' => 'https://www.talque.com/api/v1/',
            'timeout'  => 30.0,
            'headers'  => [
                'Accept' => 'application/json, text/plain, */*',
                'Content-Type' => 'application/json',
                'x-tq-app-wl' => 'Hannover Messe 2026',
                'x-tq-app-ver' => '1646',
                'x-tq-app-tgt' => 'IOS',
            ]
        ]);
    }

    /**
     * Виконати GET запит до API
     *
     * @param string $endpoint
     * @param array $params
     * @return array|null
     */
    public function get(string $endpoint, array $params = []): ?array
    {
        try {
            $response = $this->client->get($endpoint, [
                'query' => $params,
                'headers' => $this->getAuthHeaders(),
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            \Log::error('HannoverMessaService GET error', [
                'endpoint' => $endpoint,
                'params' => $params,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Виконати POST запит до API
     *
     * @param string $endpoint
     * @param array $data
     * @return array|null
     */
    public function post(string $endpoint, array $data = []): ?array
    {
        try {
            \Log::info('Making POST request', [
                'endpoint' => $endpoint,
                'data' => $data,
                'headers' => $this->getAuthHeaders()
            ]);

            $response = $this->client->post($endpoint, [
                'json' => $data,
                'headers' => $this->getAuthHeaders(),
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            \Log::info('POST request successful', [
                'endpoint' => $endpoint,
                'result' => $result
            ]);

            return $result;
        } catch (RequestException $e) {
            \Log::error('HannoverMessaService POST error', [
                'endpoint' => $endpoint,
                'data' => $data,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);

            return null;
        } catch (\Exception $e) {
            \Log::error('HannoverMessaService POST exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Отримати список експонентів
     *
     * @param array $filters
     * @return array
     */
    public function getExhibitors(array $filters = []): array
    {
        $data = $this->get('exhibitors', $filters);

        return $data ?? [];
    }

    /**
     * Отримати деталі експонента за ID
     *
     * @param int|string $exhibitorId
     * @return array|null
     */
    public function getExhibitorById($exhibitorId): ?array
    {
        return $this->get("exhibitors/{$exhibitorId}");
    }

    /**
     * Отримати список продуктів
     *
     * @param array $filters
     * @return array
     */
    public function getProducts(array $filters = []): array
    {
        $data = $this->get('products', $filters);

        return $data ?? [];
    }

    /**
     * Отримати деталі продукту за ID
     *
     * @param int|string $productId
     * @return array|null
     */
    public function getProductById($productId): ?array
    {
        return $this->get("products/{$productId}");
    }

    /**
     * Парсинг даних експонентів з пагінацією
     *
     * @param int $startPage
     * @param int $endPage
     * @param callable|null $callback
     * @return array
     */
    public function parseExhibitors(int $startPage = 1, int $endPage = 10, ?callable $callback = null): array
    {
        $results = [];

        for ($page = $startPage; $page <= $endPage; $page++) {
            $data = $this->get('exhibitors', ['page' => $page]);

            if (!$data || empty($data)) {
                break;
            }

            // Якщо передано callback функцію, викликаємо її для кожного елемента
            if ($callback && is_callable($callback)) {
                foreach ($data as $item) {
                    $callback($item);
                }
            }

            $results = array_merge($results, $data);

            // Затримка між запитами для уникнення перевантаження API
            sleep(1);
        }

        return $results;
    }

    /**
     * Пошук профілів учасників (з пагінацією)
     *
     * @param string $orgId
     * @param string|null $cursor
     * @param int $limit
     * @return array|null
     */
    public function searchOrgProfiles(string $orgId, ?string $cursor = null, int $limit = 25): ?array
    {
        $data = [
            'orgId' => $orgId,
            'limit' => $limit,
            'onlyParticipants' => true,
            'onlySpeakers' => false,
            'onlyHighlight' => false,
            'text' => '',
            'categories' => [],
            'keywords' => [],
            'seeking' => [],
            'skills' => [],
            'geo' => [
                'regions' => [],
                'country' => null
            ],
            'trivialOrder' => 'ALPHABETIC',
            // Cursor обов'язковий! Для першого запиту з curl.txt
            'cursor' => $cursor ?? 'False:',
        ];

        return $this->post('org/profile/search', $data);
    }

    /**
     * Отримати всіх учасників з пагінацією
     *
     * @param string $orgId
     * @param callable|null $callback
     * @return array
     */
    /**
     * Зібрати ВСІ ID профілів (без завантаження повних даних)
     *
     * @param string $orgId
     * @return array
     */
    public function getAllProfileIds(string $orgId): array
    {
        $allProfileIds = [];
        $cursor = null;
        $page = 1;

        do {
            \Log::info("Fetching profile IDs page {$page}");

            $response = $this->searchOrgProfiles($orgId, $cursor, 25);

            if (!$response || !$response['success']) {
                \Log::error("API request failed", ['response' => $response]);
                break;
            }

            $profileIds = $response['profiles'] ?? [];

            if (empty($profileIds) || !is_array($profileIds)) {
                \Log::warning("No profile IDs found in response");
                break;
            }

            $allProfileIds = array_merge($allProfileIds, $profileIds);
            $cursor = $response['cursor'] ?? null;

            \Log::info("Page {$page}: fetched " . count($profileIds) . " IDs, total: " . count($allProfileIds));

            $page++;
            sleep(1);

        } while ($cursor && !empty($profileIds));

        \Log::info("Total profile IDs collected: " . count($allProfileIds));

        return $allProfileIds;
    }

    /**
     * Отримати всіх учасників з повними даними
     *
     * @param string $orgId
     * @param callable|null $callback
     * @return array
     */
    public function getAllParticipants(string $orgId, ?callable $callback = null): array
    {
        // Крок 1: Зібрати всі ID
        $allProfileIds = $this->getAllProfileIds($orgId);

        // Крок 2: Завантажити повні дані порціями
        if (!empty($allProfileIds)) {
            \Log::info("Fetching full profile data for " . count($allProfileIds) . " profiles");
            return $this->parseOrgProfilesInChunks($orgId, $allProfileIds, 25, $callback);
        }

        return [];
    }

    /**
     * Отримати профілі за orgId та profileIdList (з curl.txt)
     *
     * @param string $orgId
     * @param array $profileIdList
     * @return array|null
     */
    public function getOrgProfiles(string $orgId, array $profileIdList): ?array
    {
        return $this->post('org/profile', [
            'orgId' => $orgId,
            'profileIdList' => $profileIdList,
        ]);
    }

    /**
     * Отримати всі профілі організації порціями
     *
     * @param string $orgId
     * @param array $allProfileIds
     * @param int $chunkSize
     * @param callable|null $callback
     * @return array
     */
    public function parseOrgProfilesInChunks(string $orgId, array $allProfileIds, int $chunkSize = 25, ?callable $callback = null): array
    {
        $results = [];
        $chunks = array_chunk($allProfileIds, $chunkSize);

        foreach ($chunks as $index => $profileIds) {
            \Log::info("Processing chunk " . ($index + 1) . " of " . count($chunks));

            $data = $this->getOrgProfiles($orgId, $profileIds);

            if ($data) {
                // Якщо передано callback функцію, викликаємо її для кожного елемента
                if ($callback && is_callable($callback)) {
                    foreach ($data as $item) {
                        $callback($item);
                    }
                }

                $results = array_merge($results, $data);
            }

            // Затримка між запитами для уникнення перевантаження API
            sleep(2);
        }

        return $results;
    }

    /**
     * Отримати заголовки авторизації
     *
     * @return array
     */
    protected function getAuthHeaders(): array
    {
        $headers = [];

        // Bearer token з конфігурації
        if (!empty($this->config['bearer_token'])) {
            $headers['Authorization'] = 'Bearer ' . $this->config['bearer_token'];
        }

        return $headers;
    }

    /**
     * Безпечно отримати значення з масиву за допомогою dot notation
     *
     * @param array $data
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getNestedValue(array $data, string $key, $default = null)
    {
        return Arr::get($data, $key, $default);
    }

    /**
     * Трансформувати дані експонента в потрібний формат
     *
     * @param array $exhibitor
     * @return array
     */
    public function transformExhibitor(array $exhibitor): array
    {
        return [
            'id' => $this->getNestedValue($exhibitor, 'id'),
            'name' => $this->getNestedValue($exhibitor, 'name'),
            'description' => $this->getNestedValue($exhibitor, 'description'),
            'website' => $this->getNestedValue($exhibitor, 'website'),
            'email' => $this->getNestedValue($exhibitor, 'contact.email'),
            'phone' => $this->getNestedValue($exhibitor, 'contact.phone'),
            'address' => $this->getNestedValue($exhibitor, 'contact.address'),
            'booth_number' => $this->getNestedValue($exhibitor, 'booth.number'),
            'hall' => $this->getNestedValue($exhibitor, 'booth.hall'),
            'logo_url' => $this->getNestedValue($exhibitor, 'logo_url'),
            'categories' => $this->getNestedValue($exhibitor, 'categories', []),
        ];
    }
}
