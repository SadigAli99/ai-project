<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Live\RespondRequest;
use App\Services\LiveService;

class LiveController extends Controller
{
    protected LiveService $liveService;

    public function __construct(LiveService $liveService)
    {
        $this->liveService = $liveService;
    }

    public function respond(RespondRequest $request)
    {
        $history = [];
        if ($request->filled('history')) {
            $history = json_decode($request->input('history'), true) ?: [];
        }

        $result = $this->liveService->respond(
            $request->file('audio'),
            $history,
        );

        return response()->json($result);
    }
}
