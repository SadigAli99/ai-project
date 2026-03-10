<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class AdminConversationController extends Controller
{
    public function index(Request $request)
    {
        $query = Conversation::with('user')->withCount('messages');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $conversations = $query->latest()->paginate(15)->withQueryString();

        return view('admin.conversations.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $conversation->load(['messages' => function ($query) {
            $query->with('user')->oldest();
        }, 'user']);

        return view('admin.conversations.show', compact('conversation'));
    }
}
