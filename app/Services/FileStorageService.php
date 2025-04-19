<?php

// app/Services/FileStorageService.php
namespace App\Services;

use App\Contracts\FileStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FileStorageService implements FileStorageInterface
{
    protected string $disk;

    public function __construct(string $disk)
    {
        $this->disk = $disk;
    }

    public function disk(string $disk){
        $this->disk = $disk;
        return $this;
    }

    public function store(UploadedFile $file, string $directory = 'uploads', array $rules = ['file', 'max:5120']): string
    {
        $validator = Validator::make(['file' => $file], [
            'file' => $rules,
        ]);

        if ($validator->fails()) {
            Log::error('File validation failed: ' . implode(', ', $validator->errors()->all()));
            Log::info('filename: ' . $file->getClientOriginalName());
            throw new ValidationException($validator);
        }

        $cleanName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = uniqid() . '_' . $cleanName . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $fileName;
        try {
            Storage::disk($this->disk)->put($path, file_get_contents($file));
            return $path;
        } catch (\Exception $e) {
            Log::error($this->disk . ' upload failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }

    // public function url(?string $path): ?string
    // {
    //     return $path ? Storage::disk($this->disk)->url($path) : null;
    // }
}
