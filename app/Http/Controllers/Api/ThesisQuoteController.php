<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThesisQuoteRequest;
use App\Services\PdfUploadService;
use App\Services\ThesisPricingService;
use Illuminate\Http\JsonResponse;

class ThesisQuoteController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ThesisQuoteRequest $request, PdfUploadService $uploads, ThesisPricingService $pricing): JsonResponse
    {
        $data = $request->validated();
        $file = $uploads->findTemporary($data['upload_token']);

        return response()->json(['quote' => $pricing->calculate($file, $data)]);
    }
}
