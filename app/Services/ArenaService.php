<?php

namespace App\Services;

use App\Models\People;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class ArenaService
{
    protected array $configArena;
    protected       $client;

    public function __construct()
    {
        $this->configArena = config('services.tech_arena');
        $this->client      = new Client();;
    }

    public function getPeoples()
    {
        for ($i = 269; $i < 272; $i++) {
            $data = $this->query('/7805/search/extension/91505?order=asc&page=' . $i . '&sort=group');

            foreach ($data as $datum) {
                People::updateOrCreate([
                    'uuid'       => Arr::get($datum, 'id'),
                    'first_name' => Arr::get($datum, 'first_name'),
                    'last_name'  => Arr::get($datum, 'last_name'),
                ]);
            }

            sleep(3);
        }
    }

    public function getProfileById()
    {
        $peoples = People::whereNull('type_key_translation')->get();

        foreach ($peoples as $people) {
            $data = $this->query('/7805/thing/'.$people->uuid);

            $result = [
                'headline'                          => Arr::get($data, 'headline'),
                'summary'                           => Arr::get($data, 'summary'),
                'job_title'                         => Arr::get($data, 'job_title'),
                'company_name'                      => Arr::get($data, 'company_name'),
                'location'                          => Arr::get($data, 'location'),
                'picture_url'                       => Arr::get($data, 'picture_url'),
                'type_key_translation'              => Arr::get($data, 'type_key_translation'),
                'company_description'               => Arr::get($data, 'rtm_raw.app-988-company_description-1.value'),
                'company_website'                   => Arr::get($data, 'rtm_raw.app-988-company_website-1.value'),
                'current_role'                      => implode(',', Arr::get($data, 'rtm_raw.app-988-current_role-1.value', [])),
                'hardwaresoftware_investing'        => Arr::get($data, 'rtm_raw.app-988-hardwaresoftware_investing-1.value'),
                'industry'                          => implode(',', Arr::get($data, 'rtm_raw.app-988-industry-2.value', [])),
                'investment_region'                 => implode(',', Arr::get($data, 'rtm_raw.app-988-investment_region-1.value', [])),
                'investor_type'                     => implode(',', Arr::get($data, 'rtm_raw.app-988-investor_type-1.value', [])),
                'linkedin_profile'                  => Arr::get($data, 'rtm_raw.app-988-linkedin_profile-1.value'),
                'quick_introduction_about_yourself' => Arr::get($data, 'rtm_raw.app-988-quick_introduction_about_yourself-1.value'),
                'typical_ticket_size'               => implode(',', Arr::get($data, 'rtm_raw.app-988-typical_ticket_size-1.value', [])),
                'what_are_you_looking_for'          => implode(',', Arr::get($data, 'rtm_raw.app-988-what_are_you_looking_for-1.value', [])),
                'what_is_your_investment_thesis'    => Arr::get($data, 'rtm_raw.app-988-what_is_your_investment_thesis-1.value'),
                'will_you_join_the_investor_day'    => Arr::get($data, 'rtm_raw.app-988-will_you_join_the_investor_day-1.value'),
                'investment_stage'                  => implode(',', Arr::get($data, 'rtm_raw.app-988-investment_stage-1.value', [])),
                'topics_of_interest'                => implode(',', Arr::get($data, 'rtm_raw.app-988-topics_of_interest-1.value', [])),
                'attendee_type'                     => implode(',', Arr::get($data, 'rtm_raw.app-988-event-7805-attendee_type-1.value', [])),
            ];

            $people->update($result);

            sleep(1);
        }
    }

    private function query($url)
    {
        try {
            $response = $this->client->get($this->configArena['url'] . $url, [
                'headers' => [
                    'x-authorization' => '5533b91d-f0ef-46d6-93d2-79f8113eb43b',
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            return Arr::get($result, 'data');

        } catch (RequestException $e) {
echo '<pre>';
print_r($url);
echo '</pre>';

            echo '<pre>';
            print_r($e->getResponse());
            echo '</pre>';
            die;
            die;
        }
    }

}
