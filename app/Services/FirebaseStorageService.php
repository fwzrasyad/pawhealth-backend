<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Storage;

class FirebaseStorageService
{
    protected $storage;
    protected $bucket;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
        $this->bucket = $storage->getBucket();
    }

    /**
     * Upload a file to Firebase Storage.
     *
     * @param  UploadedFile  $file    The uploaded file
     * @param  string        $folder  The folder path (e.g. 'pets', 'users', 'licenses')
     * @return string                 The public URL of the uploaded file
     */
    public function upload(UploadedFile $file, string $folder): string
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;
        $filePath = $folder . '/' . $fileName;

        $this->bucket->upload(
            fopen($file->getRealPath(), 'r'),
            [
                'name' => $filePath,
                'predefinedAcl' => 'publicRead',
            ]
        );

        $bucketName = $this->bucket->name();

        return sprintf(
            'https://storage.googleapis.com/%s/%s',
            $bucketName,
            $filePath
        );
    }

    /**
     * Delete a file from Firebase Storage by its public URL.
     *
     * @param  string|null  $url  The public URL of the file to delete
     * @return void
     */
    public function delete(?string $url): void
    {
        if (!$url) {
            return;
        }

        try {
            $bucketName = $this->bucket->name();
            $prefix = sprintf('https://storage.googleapis.com/%s/', $bucketName);

            if (str_starts_with($url, $prefix)) {
                $filePath = substr($url, strlen($prefix));
                $object = $this->bucket->object($filePath);

                if ($object->exists()) {
                    $object->delete();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Firebase Storage delete failed: ' . $e->getMessage());
        }
    }
}
