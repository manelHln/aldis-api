<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FileHandler
{
    public static function storeFile(UploadedFile $file, string $directory = 'uploads', string $disk = 's3', array $rules = ['file', 'max:5120']): string
    {
        $validator = Validator::make(['file' => $file], [
            'file' => $rules,
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $cleanName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = uniqid() . '_' . $cleanName . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $fileName;
        try {
            Storage::disk('s3')->put($path, file_get_contents($file));
            return $path;
        } catch (\Exception $e) {
            Log::error('S3 upload failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function deleteFile(?string $path, string $disk = 's3'): void
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            try{
                Storage::disk($disk)->delete($path);
                Log::info("Deleted file: $path from disk: $disk");
            }catch (\Exception $e){
                Log::error('File deletion failed: ' . $e->getMessage());
                throw $e;
            }
        }
    }
}
