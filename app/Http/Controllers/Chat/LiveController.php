<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Live\RespondRequest;
use App\Models\LiveSession;
use App\Services\LiveService;
use Illuminate\Http\Request;

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

        // Track live session
        $sessionId = $request->input('session_id');

        if ($sessionId) {
            $session = LiveSession::findOrFail($sessionId);
            $session->increment('turns_count');
        } else {
            $session = LiveSession::create([
                'user_id' => auth()->id(),
                'started_at' => now(),
                'turns_count' => 1,
            ]);
        }

        $result = $this->liveService->respond(
            $request->file('audio'),
            $history,
        );

        $result['session_id'] = $session->id;

        return response()->json($result);
    }

    public function endSession(Request $request)
    {
        $request->validate(['session_id' => 'required|integer|exists:live_sessions,id']);

        $session = LiveSession::where('id', $request->input('session_id'))
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $session->update(['ended_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
