<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface FileStorageInterface
{
    public function disk(string $disk);
    public function store(UploadedFile $file, string $directory, array $rules = ['file', 'max:5120']): string;
    public function delete(?string $path): void;
}
