<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * Upload gambar terpusat: validasi MIME asli + ukuran + nama aman.
 * Jangan pernah percaya ekstensi dari client.
 */
class ImageUpload
{
    public const MAX_SIZE_KB = 5120; // 5 MB
    public const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /** Aturan validasi untuk dipakai di Form Request / validate(). */
    public static function rules(bool $required = true): array
    {
        $rules = ['image', 'mimetypes:'.implode(',', self::ALLOWED_MIMES), 'max:'.self::MAX_SIZE_KB];

        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Simpan file dengan nama hash aman. Mengembalikan path relatif disk.
     *
     * @throws InvalidArgumentException
     */
    public static function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        if (! $file->isValid()) {
            throw new InvalidArgumentException('Upload gambar gagal. Silakan coba lagi.');
        }

        if (! in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            throw new InvalidArgumentException('Format gambar harus JPG, PNG, atau WebP.');
        }

        if ($file->getSize() > self::MAX_SIZE_KB * 1024) {
            throw new InvalidArgumentException('Ukuran gambar maksimal 5 MB.');
        }

        return $file->store($directory, $disk);
    }

    public static function delete(?string $path, string $disk = 'public'): void
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
