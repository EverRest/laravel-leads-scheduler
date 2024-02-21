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

    /**
     * Base64ToUploadedFile constructor.
     *
     * @param string $base64
     *
     * @throws Exception
     */
    public function __construct(private readonly string $base64)
    {
        $this->getFileOfBase64();
    }

    /**
     * @throws Exception
     */
    private function getFileOfBase64 (): void
    {
        try {
            $base_to_php = explode(',', $this->base64);
            $fileData = base64_decode($base_to_php[1]);
            $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
            file_put_contents($tmpFilePath, $fileData);
            $tmpFile = new File($tmpFilePath);
            $this->fileUploaded = new UploadedFile(
                $tmpFile->getPathname(),
                $tmpFile->getFilename(),
                $tmpFile->getMimeType(),
                0,
                true // Mark it as test, since the file isn't from real HTTP POST.
            );
            $this->ext = $this->fileUploaded->clientExtension();
            $this->filename = $this->fileUploaded->getClientOriginalName();
            $this->fileType = $this->fileUploaded->getMimeType();
        } catch (Exception $e) {
            throw new Exception('Base64 File is Invallid.!');
        }
    }

    /**
     * @return UploadedFile
     */
    public function file (): UploadedFile
    {
        return $this->fileUploaded;
    }

    /**
     * @return string
     */
    public function getExtension (): string
    {
        return $this->ext;
    }

    /**
     * @return string
     */
    public function getFilename (): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getFullPath (): string
    {
        return $this->filename.'.'.$this->ext;
    }

    /**
     * @return string
     */
    public function getFileType (): string
    {
        return $this->fileType;
    }

    /**
     * @return array
     */
    public function getAllinfo (): array
    {
        return [
            'file'      => $this->file(),
            'extension' => $this->getExtension(),
            'filename'  => $this->getFilename(),
            'full_path' => $this->getFullPath(),
            'file_type' => $this->getFileType()
        ];
    }
}
