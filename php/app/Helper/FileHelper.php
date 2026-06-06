<?php

namespace App\Helper;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{
    public function uploadFile(UploadedFile $file, string $directory = 'uploads')
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');

        return $path;
    }

    public function deleteFile(string $filePath)
    {
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        } else {
            \Log::warning('File not found for deletion: '.$filePath);
        }
    }
}
