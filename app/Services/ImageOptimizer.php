<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageOptimizer
{
    /**
     * Simpan gambar sebagai WebP terkompresi agar hemat storage.
     *
     * @throws RuntimeException
     */
    public static function storeWebp(UploadedFile $file, string $directory, int $maxDimension = 1600, int $quality = 75): string
    {
        $resource = imagecreatefromstring(file_get_contents($file->getRealPath()));

        if (! $resource) {
            throw new RuntimeException('Gagal membuka gambar yang diunggah.');
        }

        $width = imagesx($resource);
        $height = imagesy($resource);

        $scale = min($maxDimension / $width, $maxDimension / $height, 1);
        $targetWidth = max(1, (int) floor($width * $scale));
        $targetHeight = max(1, (int) floor($height * $scale));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $resource, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        $encoded = imagewebp($canvas, null, $quality);
        $imageData = ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($resource);

        if (! $encoded || $imageData === false) {
            throw new RuntimeException('Gagal mengompresi gambar.');
        }

        $filename = Str::uuid()->toString() . '.webp';
        $path = trim($directory, '/') . '/' . $filename;

        Storage::disk('public')->put($path, $imageData, 'public');

        return $path;
    }

    /**
     * Simpan dengan fallback ke file asli bila kompresi gagal.
     */
    public static function storeWithFallback(UploadedFile $file, string $directory, int $maxDimension = 1600, int $quality = 75): string
    {
        try {
            return self::storeWebp($file, $directory, $maxDimension, $quality);
        } catch (\Throwable $exception) {
            Log::warning('Optimasi gambar jurnal gagal, menyimpan file asli.', [
                'message' => $exception->getMessage(),
            ]);

            return $file->storePublicly($directory, 'public');
        }
    }
}
