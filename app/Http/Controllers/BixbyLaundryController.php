<?php

namespace App\Http\Controllers;

use App\Support\LaundryKnowledge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BixbyLaundryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = config('laundry-assistant.bixby_token');
        if (! is_string($token) || strlen($token) < 32) {
            return response()->json(['message' => 'The Bixby connection is not configured.'], 503);
        }
        if (! hash_equals($token, $request->bearerToken() ?? '')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate(['topic' => ['required', 'string', Rule::in(array_keys(LaundryKnowledge::topics()))]]);
        $entry = LaundryKnowledge::topics()[$data['topic']];

        // Bixby handles speech and natural-language topic selection. No customer data or AI API is used here.
        return response()->json([
            'title' => $entry['question'],
            'answer' => $entry['answer'],
            'website' => route('landing'),
        ])->header('Cache-Control', 'no-store');
    }
}
