<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\LiveSession;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $totalUsers = User::count();
        $todayUsers = User::whereDate('created_at', $today)->count();

        $totalConversations = Conversation::count();
        $todayConversations = Conversation::whereDate('created_at', $today)->count();

        $totalMessages = Message::count();
        $todayMessages = Message::whereDate('created_at', $today)->count();

        $totalLiveSessions = LiveSession::count();
        $todayLiveSessions = LiveSession::whereDate('started_at', $today)->count();

        $recentUsers = User::withCount('conversations')
            ->latest()
            ->take(10)
            ->get();

        $dailyStats = $this->getDailyStats();

        return view('admin.dashboard', compact(
            'totalUsers',
            'todayUsers',
            'totalConversations',
            'todayConversations',
            'totalMessages',
            'todayMessages',
            'totalLiveSessions',
            'todayLiveSessions',
            'recentUsers',
            'dailyStats',
        ));
    }

    protected function getDailyStats()
    {
        $from = now()->subDays(6)->toDateString();

        $messages = DB::table('messages')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as messages_count'))
            ->where('created_at', '>=', $from)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('messages_count', 'date');

        $conversations = DB::table('conversations')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as conversations_count'))
            ->where('created_at', '>=', $from)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('conversations_count', 'date');

        $liveSessions = DB::table('live_sessions')
            ->select(DB::raw('DATE(started_at) as date'), DB::raw('COUNT(*) as live_sessions_count'))
            ->where('started_at', '>=', $from)
            ->groupBy(DB::raw('DATE(started_at)'))
            ->pluck('live_sessions_count', 'date');

        $stats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $stats[] = (object) [
                'date' => $date,
                'messages_count' => $messages[$date] ?? 0,
                'conversations_count' => $conversations[$date] ?? 0,
                'live_sessions_count' => $liveSessions[$date] ?? 0,
            ];
        }

        return collect($stats);
    }
}
