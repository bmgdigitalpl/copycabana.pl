<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InPostController extends Controller
{
    public function points(Request $request, InPostService $inPost): JsonResponse
    {
        $validated = $request->validate([
            'post_code' => ['nullable', 'string', 'max:10', 'required_without:city'],
            'city' => ['nullable', 'string', 'max:100', 'required_without:post_code'],
        ]);

        return response()->json([
            'points' => $inPost->searchPoints(
                $validated['post_code'] ?? null,
                $validated['city'] ?? null,
            ),
        ]);
    }
}
