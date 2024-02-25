<?php
declare(strict_types=1);

namespace App\Helpers;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

final class Base64ToUploadedFile
{
    private string $ext;
    private string $filename;
    private string $fileType;
    private UploadedFile $fileUploaded;

    public function __construct(private readonly string $base64)
    {
        $this->convertBase64ToFile();
    }

    private function convertBase64ToFile(): void
    {
        try {
            $base_to_php = explode(',', $this->base64);
            $fileData = base64_decode($base_to_php[0]);
            $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
            file_put_contents($tmpFilePath, $fileData);
            $tmpFile = new File($tmpFilePath);
            $this->fileUploaded = new UploadedFile(
                $tmpFile->getPathname(),
                $tmpFile->getFilename(),
                $tmpFile->getMimeType(),
                0,
                true
            );
            $this->ext = $this->fileUploaded->clientExtension();
            $this->filename = $this->fileUploaded->getClientOriginalName();
            $this->fileType = $this->fileUploaded->getMimeType();
        } catch (Exception $e) {
            throw new Exception('Invalid Base64 file.');
        }
    }

    public function file(): UploadedFile
    {
        return $this->fileUploaded;
    }

    public function getExtension(): string
    {
        return $this->ext;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
