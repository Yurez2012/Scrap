<?php

namespace App\Http\Controllers;

use App\Models\HannoverProfile;
use App\Services\ArenaService;
use App\Services\ElmiaService;
use App\Services\HannoverMessaService;

class TechArenaController extends Controller
{
    protected HannoverMessaService $hannoverMessaService;

    public function __construct(HannoverMessaService $hannoverMessaService)
    {
        $this->hannoverMessaService = $hannoverMessaService;
    }

    public function index()
    {
        $orgId = config('services.hannover_messa.org_id');

        // Швидкий варіант - тільки один запит, 25 ID
        $response = $this->hannoverMessaService->searchOrgProfiles($orgId);

        if ($response && $response['success']) {
            // Завантажити повні дані тільки для цих 25 профілів
            $profileIds = $response['profiles'] ?? [];
            $profiles = $this->hannoverMessaService->getOrgProfiles($orgId, $profileIds);

            return [
                'total_ids' => count($profileIds),
                'profiles' => $profiles,
                'cursor' => $response['cursor']
            ];
        }

        return $response;
    }

    /**
     * Зібрати ВСІ ID профілів учасників
     */
    public function getAllProfileIds()
    {
        $orgId = config('services.hannover_messa.org_id');
        $profileIds = $this->hannoverMessaService->getAllProfileIds($orgId);

        return response()->json([
            'total' => count($profileIds),
            'profile_ids' => $profileIds
        ]);
    }

    /**
     * Зберегти profile IDs в базу даних
     */
    public function saveProfileIds()
    {
        $orgId = config('services.hannover_messa.org_id');

        \Log::info('Starting to save profile IDs to database');

        // Отримати всі profile IDs з API
        $profileIds = $this->hannoverMessaService->getAllProfileIds($orgId);

        if (empty($profileIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No profile IDs found'
            ]);
        }

        $saved = 0;
        $skipped = 0;

        // Зберегти кожен profile_id в базу
        foreach ($profileIds as $index => $profileId) {
            $exists = HannoverProfile::where('profile_id', $profileId)->exists();

            if (!$exists) {
                HannoverProfile::create([
                    'profile_id' => $profileId,
                    'data_fetched' => false,
                ]);
                $saved++;
            } else {
                $skipped++;
            }

            // Затримка кожні 50 записів, щоб не перевантажувати БД
            if (($index + 1) % 50 === 0) {
                $processed = $index + 1;
                \Log::info("Processed {$processed} profile IDs, sleeping for 1 second");
                sleep(1);
            }
        }

        \Log::info('Profile IDs saved', [
            'total' => count($profileIds),
            'saved' => $saved,
            'skipped' => $skipped
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile IDs saved successfully',
            'total' => count($profileIds),
            'saved' => $saved,
            'skipped' => $skipped
        ]);
    }

    /**
     * Завантажити повні дані профілів з БД
     */
    public function fetchProfilesData()
    {
        $orgId = config('services.hannover_messa.org_id');

        \Log::info('Starting to fetch full profile data');

        // Отримати всі profile IDs, де ще не завантажено дані
        $profiles = HannoverProfile::where('data_fetched', false)->get();

        if ($profiles->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'All profiles already have data fetched',
                'total' => 0
            ]);
        }

        $profileIds = $profiles->pluck('profile_id')->toArray();
        $processed = 0;
        $failed = 0;

        // Обробляти по 25 профілів за раз
        $chunks = array_chunk($profileIds, 25);

        foreach ($chunks as $index => $chunk) {
            \Log::info("Processing chunk " . ($index + 1) . " of " . count($chunks));

            $profilesData = $this->hannoverMessaService->getOrgProfiles($orgId, $chunk);

            if ($profilesData && is_array($profilesData)) {
                // API повертає дані у форматі {"profileId": {"model": {...}}}
                $profileById = $profilesData['profileById'] ?? $profilesData;

                foreach ($profileById as $profileId => $data) {
                    if (!isset($data['model'])) {
                        continue;
                    }

                    $modelData = $data['model'];
                    $profile = HannoverProfile::where('profile_id', $profileId)->first();

                    if ($profile) {
                        $profile->update([
                            'first_name' => $modelData['firstName'] ?? null,
                            'last_name' => $modelData['lastName'] ?? null,
                            'job_title' => $modelData['jobTitle'] ?? null,
                            'company_name' => $modelData['companyName'] ?? null,
                            'bio' => $modelData['bio'] ?? null,
                            'photo_url' => $modelData['photo'] ?? null,
                            'email' => $modelData['email'] ?? null,
                            'phone' => $modelData['phone'] ?? null,
                            'linkedin_url' => $modelData['linkedin'] ?? null,
                            'website' => $modelData['website'] ?? null,
                            'raw_data' => $modelData,
                            'data_fetched' => true,
                        ]);
                        $processed++;
                    }
                }
            } else {
                $failed += count($chunk);
                \Log::error("Failed to fetch chunk " . ($index + 1));
            }

            // Затримка між запитами
            sleep(2);
        }

        \Log::info('Profile data fetching completed', [
            'processed' => $processed,
            'failed' => $failed
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile data fetched successfully',
            'processed' => $processed,
            'failed' => $failed,
            'total_chunks' => count($chunks)
        ]);
    }

    /**
     * Отримати експонентів з Hannover Messa
     */
    public function getHannoverExhibitors()
    {
        $exhibitors = $this->hannoverMessaService->getExhibitors();

        return response()->json($exhibitors);
    }

    /**
     * Отримати список ID профілів для нетворкінгу
     */
    public function getNetworkingProfileIds()
    {
        $orgId = config('services.hannover_messa.org_id');
        $profileIds = $this->hannoverMessaService->getNetworkingProfileIds($orgId);

        return response()->json([
            'org_id' => $orgId,
            'profile_ids' => $profileIds,
            'count' => count($profileIds)
        ]);
    }

    /**
     * Повний процес: отримати ID профілів і завантажити всі дані
     */
    public function fetchNetworkingData()
    {
        $orgId = config('services.hannover_messa.org_id');

        // Крок 1: Отримати всі ID профілів для нетворкінгу
        $profileIds = $this->hannoverMessaService->getNetworkingProfileIds($orgId);

        if (empty($profileIds)) {
            return response()->json([
                'message' => 'No networking profiles found',
                'count' => 0
            ]);
        }

        // Крок 2: Завантажити дані по всіх профілях порціями
        $results = $this->hannoverMessaService->parseOrgProfilesInChunks($orgId, $profileIds, 25, function($profile) {
            // Тут можна зберігати дані в БД
            \Log::info('Processing networking profile', [
                'id' => $profile['id'] ?? 'Unknown',
                'name' => $profile['name'] ?? 'Unknown'
            ]);

            // Приклад збереження в БД:
            // People::updateOrCreate(
            //     ['profile_id' => $profile['id']],
            //     [
            //         'name' => $profile['name'],
            //         'email' => $profile['email'],
            //         // ... інші поля
            //     ]
            // );
        });

        return response()->json([
            'message' => 'Networking data fetched successfully',
            'profile_count' => count($profileIds),
            'processed_count' => count($results)
        ]);
    }

    /**
     * Отримати деталі експонента за ID
     */
    public function getHannoverExhibitor($id)
    {
        $exhibitor = $this->hannoverMessaService->getExhibitorById($id);

        if (!$exhibitor) {
            return response()->json(['error' => 'Exhibitor not found'], 404);
        }

        return response()->json($exhibitor);
    }

    /**
     * Парсити експонентів з пагінацією
     */
    public function parseHannoverExhibitors()
    {
        // Парсити експонентів з 1 по 10 сторінку
        $results = $this->hannoverMessaService->parseExhibitors(1, 10, function($exhibitor) {
            // Можна зберігати в БД або обробляти дані
            // Наприклад: Exhibitor::updateOrCreate([...]);
            \Log::info('Processing exhibitor', ['name' => $exhibitor['name'] ?? 'Unknown']);
        });

        return response()->json([
            'message' => 'Parsing completed',
            'count' => count($results)
        ]);
    }

    /**
     * Отримати профілі організації за списком ID
     */
    public function getOrgProfiles()
    {
        $orgId = config('services.hannover_messa.org_id');

        // Приклад списку profile IDs з curl.txt
        $profileIds = [
            "IA7x2IrtQ2wGeWhdF2aU", "90JwGRVq8bWfc6ysFh8U", "fouJRt5MdKrclHckBfJ5",
            "lVsGu0UP428sGj6ozWSq", "RwsK5fsWcYJOu3ocyV7X", "PtldPmrkcEYECjL2ugAv",
            "OHDpCI1AZ6zW2MVxbEmo", "rYMREDM2XgCcy718yyTw", "SrXvC1wIayzrm9HjUtgL",
            "gowd0XntQhNuvtfmZuO", "ehTz0X3zUX1e4iWRqmVu", "rWUqXtukN7kYtPQdHIxf",
            "h9IUbQujElXVhQ23SJDW", "7muj4gGNi6sVFmG2jaxE", "QDLWvUT7pF6KInIkzcyj",
            "52b63i6CqwvmrvDtZwop", "XUCZR8HAf8IEzYRIgkz0", "lHnvtjNEooDuXnrqj1oW",
            "pLNR81gY2oPLfvfro5lf", "rP9hyxCFveeH9yuzcyxw", "VwOPfdPYnz3LGZO5aOMN",
            "SX7kV5u75fMa4GUIVdQv", "pbXdhX5J8iVWXp5L1DR7", "vwCQaeeHZkL079lOztKd",
            "ZCPqcxVsU6aLLsiYLXNa"
        ];

        $profiles = $this->hannoverMessaService->getOrgProfiles($orgId, $profileIds);

        return response()->json($profiles);
    }

    /**
     * Парсити всі профілі організації порціями
     */
    public function parseOrgProfiles()
    {
        $orgId = config('services.hannover_messa.org_id');

        // Тут треба отримати всі profile IDs з якогось джерела
        // Для прикладу використовуємо список з curl.txt
        $allProfileIds = [
            "IA7x2IrtQ2wGeWhdF2aU", "90JwGRVq8bWfc6ysFh8U", "fouJRt5MdKrclHckBfJ5",
            "lVsGu0UP428sGj6ozWSq", "RwsK5fsWcYJOu3ocyV7X", "PtldPmrkcEYECjL2ugAv",
            "OHDpCI1AZ6zW2MVxbEmo", "rYMREDM2XgCcy718yyTw", "SrXvC1wIayzrm9HjUtgL",
            "gowd0XntQhNuvtfmZuO", "ehTz0X3zUX1e4iWRqmVu", "rWUqXtukN7kYtPQdHIxf",
            "h9IUbQujElXVhQ23SJDW", "7muj4gGNi6sVFmG2jaxE", "QDLWvUT7pF6KInIkzcyj",
            "52b63i6CqwvmrvDtZwop", "XUCZR8HAf8IEzYRIgkz0", "lHnvtjNEooDuXnrqj1oW",
            "pLNR81gY2oPLfvfro5lf", "rP9hyxCFveeH9yuzcyxw", "VwOPfdPYnz3LGZO5aOMN",
            "SX7kV5u75fMa4GUIVdQv", "pbXdhX5J8iVWXp5L1DR7", "vwCQaeeHZkL079lOztKd",
            "ZCPqcxVsU6aLLsiYLXNa"
        ];

        // Парсити по 25 ID за раз
        $results = $this->hannoverMessaService->parseOrgProfilesInChunks($orgId, $allProfileIds, 25, function($profile) {
            // Можна зберігати в БД або обробляти дані
            \Log::info('Processing profile', [
                'id' => $profile['id'] ?? 'Unknown',
                'name' => $profile['name'] ?? 'Unknown'
            ]);
        });

        return response()->json([
            'message' => 'Parsing completed',
            'count' => count($results)
        ]);
    }
}
