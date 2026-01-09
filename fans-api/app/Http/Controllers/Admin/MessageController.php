<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\AutomatedMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MessageController extends Controller
{
    /**
     * Get message statistics
     */
    public function getStats()
    {
        $now = Carbon::now();
        $startOfDay = $now->copy()->startOfDay();

        $stats = [
            'total_messages' => Message::count(),
            'messages_today' => Message::where('created_at', '>=', $startOfDay)->count(),
            'unread_messages' => Message::whereNull('read_at')->count(),
            'reported_messages' => Message::where('status', Message::STATUS_REPORTED)->count()
        ];

        return response()->json($stats);
    }

    /**
     * Get paginated messages with filters
     */
    public function index(Request $request)
    {
        $query = Message::with(['sender', 'receiver', 'media.previews', 'transactions'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->type) {
            switch ($request->type) {
                case 'automated':
                    $query->where('is_automated', true);
                    break;
                case 'tip':
                    $query->whereHas('transactions');
                    break;
                case 'regular':
                    $query->where('is_automated', false)
                        ->whereDoesntHave('transactions');
                    break;
            }
        }

        if ($request->status) {
            switch ($request->status) {
                case 'read':
                    $query->whereNotNull('read_at');
                    break;
                case 'unread':
                    $query->whereNull('read_at');
                    break;
                case 'reported':
                    $query->where('status', Message::STATUS_REPORTED);
                    break;
            }
        }

        if ($request->start_date) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }

        if ($request->end_date) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('sender', function ($q) use ($search) {
                        $q->where('username', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('receiver', function ($q) use ($search) {
                        $q->where('username', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        $messages = $query->paginate(20);

        // Transform messages to include type
        $messages->getCollection()->transform(function ($message) {
            $message->type = $this->getMessageType($message);
            return $message;
        });

        return response()->json($messages);
    }

    /**
     * Get message details
     */
    public function show($id)
    {
        $message = Message::with(['sender', 'receiver', 'media.previews', 'transactions'])
            ->findOrFail($id);

        $message->type = $this->getMessageType($message);

        return response()->json($message);
    }

    /**
     * Review a reported message
     */
    public function review($id)
    {
        $message = Message::findOrFail($id);
        
        if ($message->status === Message::STATUS_REPORTED) {
            $message->update([
                'status' => 'reviewed',
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id()
            ]);
        }

        return response()->json(['message' => 'Message reviewed successfully']);
    }

    /**
     * Delete a message
     */
    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }

    /**
     * Helper method to determine message type
     */
    private function getMessageType($message)
    {
        if ($message->is_automated) {
            return 'automated';
        }
        
        if ($message->transactions()->exists()) {
            return 'tip';
        }
        
        return 'regular';
    }
} 