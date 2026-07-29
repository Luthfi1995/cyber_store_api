<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * GET /api/v1/chats
     * Daftar semua chat milik customer yang login.
     */
    public function index(Request $request): JsonResponse
    {
        $chats = Chat::with(['lastMessage'])
            ->where('customer_id', $request->user()->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Chat $chat) {
                $unread = $chat->messages()
                    ->where('sender_type', 'admin')
                    ->where('is_read', false)
                    ->count();

                return [
                    'id'           => $chat->id,
                    'subject'      => $chat->subject,
                    'product_id'   => $chat->product_id,
                    'product_name' => $chat->product_name,
                    'status'       => $chat->status,
                    'unread_count' => $unread,
                    'last_message' => $chat->lastMessage?->message,
                    'last_message_at' => $chat->last_message_at?->toIso8601String(),
                    'created_at'   => $chat->created_at->toIso8601String(),
                ];
            });

        return response()->json(['chats' => $chats]);
    }

    /**
     * POST /api/v1/chats
     * Mulai percakapan baru (atau ambil existing open chat untuk produk yang sama).
     * Body: { product_id?, subject?, message }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'message'    => 'required|string|max:5000',
            'product_id' => 'nullable|exists:products,id',
            'subject'    => 'nullable|string|max:255',
        ]);

        $user      = $request->user();
        $productId = $request->input('product_id');
        $product   = $productId ? Product::find($productId) : null;

        // Cek apakah ada chat open untuk produk yang sama (hindari duplikat)
        $chat = Chat::where('customer_id', $user->id)
            ->where('product_id', $productId)
            ->where('status', 'open')
            ->first();

        if (!$chat) {
            $chat = Chat::create([
                'customer_id'  => $user->id,
                'product_id'   => $productId,
                'product_name' => $product?->name,
                'subject'      => $request->input('subject', $product ? "Tanya stok: {$product->name}" : 'Pertanyaan'),
                'status'       => 'open',
                'last_message_at' => now(),
            ]);
        }

        // Simpan pesan pertama
        $message = ChatMessage::create([
            'chat_id'     => $chat->id,
            'sender_type' => 'customer',
            'sender_id'   => $user->id,
            'message'     => $request->input('message'),
            'is_read'     => false,
        ]);

        $chat->update(['last_message_at' => now()]);

        return response()->json([
            'chat'    => ['id' => $chat->id, 'subject' => $chat->subject, 'status' => $chat->status],
            'message' => $this->formatMessage($message),
        ], 201);
    }

    /**
     * GET /api/v1/chats/{chat}/messages
     * Ambil semua pesan dalam chat (polling).
     */
    public function messages(Request $request, Chat $chat): JsonResponse
    {
        // Pastikan hanya customer pemilik chat yang bisa akses
        if ($chat->customer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Tandai pesan admin sebagai sudah dibaca
        $chat->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $chat->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => $this->formatMessage($m));

        return response()->json([
            'chat_id' => $chat->id,
            'status'  => $chat->status,
            'messages' => $messages,
        ]);
    }

    /**
     * POST /api/v1/chats/{chat}/messages
     * Kirim pesan dari customer.
     */
    public function sendMessage(Request $request, Chat $chat): JsonResponse
    {
        if ($chat->customer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($chat->status === 'closed') {
            return response()->json(['message' => 'Chat sudah ditutup.'], 422);
        }

        $request->validate(['message' => 'required|string|max:5000']);

        $message = ChatMessage::create([
            'chat_id'     => $chat->id,
            'sender_type' => 'customer',
            'sender_id'   => $request->user()->id,
            'message'     => $request->input('message'),
            'is_read'     => false,
        ]);

        $chat->update(['last_message_at' => now()]);

        return response()->json(['message' => $this->formatMessage($message)], 201);
    }

    private function formatMessage(ChatMessage $message): array
    {
        return [
            'id'          => $message->id,
            'sender_type' => $message->sender_type,
            'message'     => $message->message,
            'is_read'     => $message->is_read,
            'created_at'  => $message->created_at->toIso8601String(),
        ];
    }
}
