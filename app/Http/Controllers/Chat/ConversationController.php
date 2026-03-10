<?php

namespace App\Http\Controllers\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversation\StoreRequest;
use App\Http\Requests\Conversation\UpdateRequest;
use App\Jobs\ProcessAudioMessageJob;
use App\Jobs\ProcessTextMessageJob;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    protected $conversationService;

    public function __construct(
        ConversationService $conversationService
    ) {
        $this->conversationService = $conversationService;
    }

    public function index(Request $request)
    {
        $conversations = $this->conversationService->list($request);
        return response()->json([
            'items' => $conversations->items(),
            'current_page' => $conversations->currentPage(),
            'last_page' => $conversations->lastPage(),
            'total' => $conversations->total(),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $user = request()->user();

        [$conversation, $message] = $this->conversationService->createWithFirstMessage(
            $user,
            $request->get('message_type'),
            $request->get('content'),
            $request->file('audio')
        );

        broadcast(new MessageSent($message))->toOthers();

        if ($request->get('message_type') == 'text') ProcessTextMessageJob::dispatch($message->id);
        else if ($request->get('message_type') == 'audio') ProcessAudioMessageJob::dispatch($message->id);

        return response()->json([
            'conversation' => $conversation,
            'first_message' => $message->load('user:id,name')
        ], 201);
    }

    public function update(UpdateRequest $request, Conversation $conversation)
    {
        $conversation = $this->conversationService->rename($request->user(), $conversation, $request->get('title'));
        return response()->json($conversation);
    }

    public function delete(Conversation $conversation){
        $this->conversationService->delete(request()->user(), $conversation);
        return response()->json(['ok' => 'Successfully deleted']);
    }
}
