<?php

namespace App\Http\Controllers;

use App\Models\PeopleTransform;
use App\Services\ArenaService;
use App\Services\TransformService;

class TechArenaController extends Controller
{
    public function __construct(protected TransformService $transformService)
    {
    }

    public function index()
    {
     $this->transformService->storePeopleData();

        die('finish');
    }
}
