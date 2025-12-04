<?php

namespace App\Http\Controllers;

use App\Services\AI\GigaChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GigaChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:1000'
            ]);
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'error' => true,
                    'message' => 'Пользователь не авторизован'
                ], 401);
            }
            $chat = new GigaChatService;
            $result = $chat->sendMessage($validated['message'], $userId);
             if (isset($result['error']) && $result['error']) {
                return response()->json([
                    'error' => true,
                    'message' => $result['message'] ?? 'Неизвестная ошибка'
                ], $result['status'] ?? 500);
            }
            return response()->json([
                'success' => true,
                'content' => $result['choices'][0]['message']['content'] ?? 'Ответ не содержит контента',
                'full_response' => $result // Опционально, для отладки
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ошибка сервера: ' . $e->getMessage()
            ], 500);
        }
    }
}
