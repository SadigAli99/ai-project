<?php

namespace App\Http\Controllers\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreAudioRequest;
use App\Http\Requests\Message\StoreTextRequest;
use App\Jobs\ProcessAudioMessageJob;
use App\Jobs\ProcessTextMessageJob;
use App\Models\Conversation;
use App\Services\ConversationService;
use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected $conversationService;
    protected $messageService;

    public function __construct(
        ConversationService $conversationService,
        MessageService $messageService,
    ) {
        $this->messageService = $messageService;
        $this->conversationService = $conversationService;
    }

    public function index(Request $request, Conversation $conversation)
    {
        $messages = $this->conversationService->messages($conversation);
        return response()->json($messages);
    }

    public function sendText(StoreTextRequest $request, Conversation $conversation)
    {
        $message = $this->messageService->sendText($request->user(), $conversation, $request->get('content'));

        broadcast(new MessageSent($message))->toOthers();
        ProcessTextMessageJob::dispatch($message->id);
        return response()->json($message->load('user:id,name'), 201);
    }

    public function sendAudio(StoreAudioRequest $request, Conversation $conversation)
    {
        $message = $this->messageService->sendAudio($request->user(), $conversation, $request->file('audio'));
        broadcast(new MessageSent($message))->toOthers();
        ProcessAudioMessageJob::dispatch($message->id);
        return response()->json($message->load('user:id,name'), 201);
    }
}
