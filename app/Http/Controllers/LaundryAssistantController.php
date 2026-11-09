<?php

namespace App\Http\Controllers;

use App\Services\LaundryAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaundryAssistantController extends Controller
{
    public function __invoke(Request $request, LaundryAssistant $assistant): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['sometimes', 'array', 'max:8'],
            'history.*' => ['required', 'array:role,content'],
            'history.*.role' => ['required', 'string', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:4000'],
        ]);

        return response()->json($assistant->reply(trim($data['message']), $data['history'] ?? []))
            ->header('Cache-Control', 'no-store');
    }
}
