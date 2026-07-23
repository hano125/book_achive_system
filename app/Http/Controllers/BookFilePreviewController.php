<?php

namespace App\Http\Controllers;

use App\Models\BookFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookFilePreviewController extends Controller
{
    public function __invoke(BookFile $file): StreamedResponse
    {
        abort_unless($file->isPdf() || $file->isImage(), 415);
        abort_unless($file->existsInStorage(), 404);

        return Storage::disk($file->disk)->response(
            $file->file_path,
            $file->original_name,
            [
                'Content-Type' => $file->mime_type,
                'Content-Disposition' => 'inline',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
