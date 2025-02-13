<?php

namespace App\Http\Controllers;

use App\Services\ArenaService;

class TechArenaController extends Controller
{
    public function __construct(protected ArenaService $arenaService)
    {
    }

    public function index()
    {
        $this->arenaService->getProfileById();

        die('finish');
    }
}
