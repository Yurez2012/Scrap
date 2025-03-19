<?php

namespace App\Services;

use App\Models\PeopleTransform;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class TransformService
{
    /**
     * @param $viewId
     * @param $endCursor
     *
     * @return array
     * @throws ConnectionException
     */
    public function fetchAllPeople($viewId, $endCursor)
    {
        $response = Http::withHeaders($this->getHeader())->post('https://app.transform.show/api/graphql', $this->getParamsAllPeople($viewId, $endCursor));

        $response = json_decode($response->body());

        return Arr::get($response, '0', [])->data->view->people ?? [];
    }

    /**
     * @param $personId
     * @param $userId
     *
     * @return array|\ArrayAccess|mixed
     * @throws ConnectionException
     */
    public function fetchPeople($personId, $userId)
    {
        $response = Http::withHeaders($this->getHeader())->post('https://app.transform.show/api/graphql', $this->getParamsByPeople($personId, $userId));

        return Arr::get($response, 'data.person');
    }

    /**
     * @param $personId
     * @param $userId
     *
     * @return array
     */
    protected function getParamsByPeople($personId, $userId)
    {
        return [
            'operationName' => 'EventPersonDetailsQuery',
            'variables'     => [
                'skipMeetings' => true,
                'withEvent'    => true,
                'personId'     => $personId,
                'userId'       => '',
                'eventId'      => $userId,
            ],
            'extensions'    => [
                'persistedQuery' => [
                    'version'    => 1,
                    'sha256Hash' => '03e6ab3182b93582753b79d92ee01125bd74c7164986e7870be9dcad9080f048',
                ],
            ],
        ];
    }

    /**
     * @param $viewId
     * @param $endCursor
     *
     * @return void
     * @throws ConnectionException
     */
    public function storeAllPeople($viewId = 'RXZlbnRWaWV3Xzc4Njg1Ng==', $endCursor = 'WzEuNzMwNDY4OCwiUlhabGJuUlFaVzl3YkdWZk16Z3hNRGM0T0RFPSJd')
    {
        $result = $this->fetchAllPeople($viewId, $endCursor);

        foreach ($result->nodes as $item) {
            PeopleTransform::updateOrCreate([
                'person_uuid' => $item->id,
                'user_uuid'   => $item->userInfo->id,
            ]);
        }

        sleep(1);

        if ($result->pageInfo->hasNextPage) {
            $this->storeAllPeople($viewId, $result->pageInfo->endCursor);
        }
    }

    protected function getHeader()
    {
        return [
            'accept'             => '*/*',
            'accept-language'    => 'uk-UA,uk;q=0.9,en-US;q=0.8,en;q=0.7',
            'authorization'      => 'Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb3JlQXBpVXNlcklkIjoiVlhObGNsOHlNemN4TmpVM01RPT0iLCJwZXJtaXNzaW9ucyI6WyJhcHBsaWNhdGlvbjpRWEJ3YkdsallYUnBiMjVmTVRNNU13PT0iLCJzY2hlbWE6dXNlciJdLCJzZXNzaW9uSWQiOiI2N2Q5YTE2ZGVhMWVmOTFhY2MzZGI2MzIiLCJ0eXBlIjoiYWNjZXNzLXRva2VuIiwidXNlcklkIjoiNjdkMTRjNjIwODNlNWQ1MGVlODk5M2QyIiwiZW1haWxWZXJpZmllZCI6dHJ1ZSwiaWF0IjoxNzQyMzE1ODk0LCJleHAiOjE3NDI0MDIyOTQsImlzcyI6ImF1dGgtYXBpIn0.V8icBW4xPCCAj6ne8e6sQO5whB5xmnMeWq-LUjyLUPtcmDmK-dUFf5p3O5tZ-HY6E8sNlRXdBwb2fpPRPjVjGajF9lm9oBozLADxOVtYktcfWM9dvIMsgNBBTausFdIRLrjuZ2wbH8OhnrK09gluTZHhc986GHnYYf0FgSa0CTn0oYIunlAYZe89zPwVoJHiYbOIaH6bJ8H2-i0QYPw_TEFd-xwJ9cy8kjzPMe-WtGnc9FuDO-9J_897jyBabNAyU8xvNcakQwjFCDIKEKUe6bBtzdocKWf_c3vPf7_tAUvXCBBSsmjLp8Nz7jVRvKrhr4amXNb6IETlj96NgKV2A3QB4XA_08EiBKfjOgZtpnJOwV009cCF5cTKBcPL-JS2QesAp7UQhms8H5H4ksDUjQ95o8-4HheiCi28lfhqCZZkM9kDAruDXMiH40bnf_LKSALUwwLjsd79mmeZIZqo4ZkSt4MD4D_ysD1UEznKntgvZTWrxfMJ9N4B297uzTSD876mdv2CsWKa7GfBh6G3Ki4GVUj0j8cUoaknYGd9gyN_zWuzMtADEwtqqAUwJN4XHaeq-aG8dIbWMvf1NAEksuc59Qm3NHEPoyiZa29UVGSdO7jgbcXFV0RzI7KmKXPDAwvwUw1i2vGjCKPu0869O5wahISy8eWmVSqrmsnsWjs',
            'origin'             => 'https://app.transform.show',
            'priority'           => 'u=1, i',
            'referer'            => 'https://app.transform.show/',
            'sec-ch-ua'          => '"Chromium";v="134", "Not:A-Brand";v="24", "Google Chrome";v="134"',
            'sec-ch-ua-mobile'   => '?1',
            'sec-ch-ua-platform' => '"Android"',
            'sec-fetch-dest'     => 'empty',
            'sec-fetch-mode'     => 'cors',
            'sec-fetch-site'     => 'same-origin',
            'x-client-origin'    => 'app.transform.show',
            'x-client-platform'  => 'Event App',
            'x-client-version'   => '2.309.140',
            'x-feature-flags'    => 'fixBackwardPaginationOrder',
        ];
    }

    protected function getParamsAllPeople($viewId, $endCursor)
    {
        return [
            [
                'operationName' => 'EventPeopleListViewConnectionQuery',
                'variables'     => [
                    'viewId'    => $viewId,
                    'endCursor' => $endCursor,
                ],
                'extensions'    => [
                    'persistedQuery' => [
                        'version'    => 1,
                        'sha256Hash' => '7f6aeac87634ef772c93d5b0b2e89c9e7ed810a19868180507be401b9ab18214',
                    ],
                ],
            ],
        ];
    }

    public function storePeopleData()
    {
        $peoples = PeopleTransform::whereNull('firstName')->get();

        foreach ($peoples as $people) {
            $data = $this->fetchPeople($people->person_uuid, $people->user_uuid);

            $social = '';

            foreach (Arr::get($data, 'socialNetworks', []) as $item) {
                $social .= Arr::get($item, 'type') . ' => ' . Arr::get($item, 'profile') . "\n";
            }

            $people->update([
                'address'        => Arr::get($data, 'address'),
                'biography'      => Arr::get($data, 'biography'),
                'email'          => Arr::get($data, 'email'),
                'firstName'      => Arr::get($data, 'firstName'),
                'lastName'       => Arr::get($data, 'lastName'),
                'jobTitle'       => Arr::get($data, 'jobTitle'),
                'mobilePhone'    => Arr::get($data, 'mobilePhone'),
                'organization'   => Arr::get($data, 'organization'),
                'socialNetworks' => $social,
                'websiteUrl'     => Arr::get($data, 'websiteUrl'),
            ]);

            sleep(1);
        }
    }
}
