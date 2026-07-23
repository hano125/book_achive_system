<?php

namespace App\Http\Controllers;

use App\Models\BookFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookFileDownloadController extends Controller
{
    public function __invoke(BookFile $file): StreamedResponse
    {
        abort_unless($file->existsInStorage(), 404);

        return Storage::disk($file->disk)->download(
            $file->file_path,
            $file->original_name,
            ['X-Content-Type-Options' => 'nosniff'],
        );
    }
}
