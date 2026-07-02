<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * GET /admin/chats
     * Daftar semua percakapan customer.
     */
    public function index(Request $request)
    {
        $query = Chat::with(['customer', 'lastMessage'])
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('sender_type', 'customer')->where('is_read', false);
            }])
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($u) => $u->where('name', 'like', "%{$search}%")
                                                         ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $chats = $query->paginate(20)->withQueryString();

        // Total unread untuk badge di navbar
        $totalUnread = Chat::whereHas('messages', fn ($q) =>
            $q->where('sender_type', 'customer')->where('is_read', false)
        )->count();

        if ($request->wantsJson()) {
            return response()->json([
                'chats' => $chats->items(),
                'total_unread' => $totalUnread
            ]);
        }

        return view('admin.chats.index', compact('chats', 'totalUnread'));
    }

    /**
     * GET /admin/chats/{chat}
     * Detail percakapan + form balas.
     */
    public function show(Chat $chat)
    {
        $chat->load(['customer', 'product', 'messages.sender']);

        // Tandai semua pesan customer sebagai sudah dibaca oleh admin
        $chat->messages()
            ->where('sender_type', 'customer')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $chats = Chat::with(['customer', 'lastMessage'])
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('sender_type', 'customer')->where('is_read', false);
            }])
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        $totalUnread = Chat::whereHas('messages', fn ($q) =>
            $q->where('sender_type', 'customer')->where('is_read', false)
        )->count();

        if (request()->wantsJson()) {
            return response()->json([
                'chat' => $chat,
                'messages' => $chat->messages->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'sender_type' => $m->sender_type,
                        'sender_name' => $m->sender?->name ?? 'Admin',
                        'message' => $m->message,
                        'is_read' => $m->is_read,
                        'created_at' => $m->created_at->toIso8601String()
                    ];
                })
            ]);
        }

        return view('admin.chats.index', compact('chat', 'chats', 'totalUnread'));
    }

    /**
     * POST /admin/chats/{chat}/reply
     * Admin membalas pesan.
     */
    public function reply(Request $request, Chat $chat)
    {
        $request->validate(['message' => 'required|string|max:10000000']);

        if ($chat->status === 'closed') {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Chat sudah ditutup.'], 422);
            }
            return back()->with('error', 'Chat sudah ditutup.');
        }

        $msg = ChatMessage::create([
            'chat_id'     => $chat->id,
            'sender_type' => 'admin',
            'sender_id'   => auth()->id(),
            'message'     => $request->input('message'),
            'is_read'     => false,
        ]);

        $chat->update(['last_message_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'sender_name' => auth()->user()->name,
                    'message' => $msg->message,
                    'is_read' => $msg->is_read,
                    'created_at' => $msg->created_at->toIso8601String()
                ]
            ]);
        }

        return back()->with('success', 'Balasan terkirim.');
    }

    /**
     * POST /admin/chats/{chat}/close
     * Tutup percakapan.
     */
    public function close(Chat $chat)
    {
        $chat->update(['status' => 'closed']);
        return back()->with('success', 'Chat ditutup.');
    }

    /**
     * POST /admin/chats/{chat}/reopen
     * Buka kembali percakapan.
     */
    public function reopen(Chat $chat)
    {
        $chat->update(['status' => 'open']);
        return back()->with('success', 'Chat dibuka kembali.');
    }

    /**
     * GET /admin/chats/unread-count (JSON — untuk badge polling JS)
     */
    public function unreadCount()
    {
        $count = Chat::whereHas('messages', fn ($q) =>
            $q->where('sender_type', 'customer')->where('is_read', false)
        )->count();

        return response()->json(['unread' => $count]);
    }
}
