<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadPdfRequest;
use App\Services\PdfUploadService;
use Illuminate\Http\JsonResponse;

class OrderUploadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UploadPdfRequest $request, PdfUploadService $uploads): JsonResponse
    {
        $upload = $uploads->store($request->file('file'));

        return response()->json([
            'upload_token' => $upload['token'],
            'file' => [
                'name' => $upload['file']->original_name,
                'size' => $upload['file']->size,
                'pages' => $upload['file']->pages,
                'color_pages' => $upload['file']->color_pages,
                'bw_pages' => $upload['file']->bw_pages,
            ],
        ], 201);
    }
}
