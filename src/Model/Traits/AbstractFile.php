<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model\Traits;

use Doctrine\ORM\Mapping as ORM;
use Exception;
use GraphQL\Doctrine\Annotation as API;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Wrapping class for an uploaded file
 */
trait AbstractFile
{
    /**
     * Get base path where the files are stored in the server
     *
     * @return string
     */
    abstract protected function getBasePath(): string;

    /**
     * Get list of accepted MIME types
     *
     * @return string[]
     */
    abstract protected function getAcceptedMimeTypes(): array;

    /**
     * @var string
     *
     * @API\Exclude
     *
     * @ORM\Column(type="string", length=190, options={"default" = ""})
     */
    private $filename = '';

    /**
     * @var string
     * @ORM\Column(type="string", length=255, options={"default" = ""})
     */
    private $mime = '';

    /**
     * Set the file
     *
     * @param UploadedFileInterface $file
     */
    public function setFile(UploadedFileInterface $file): void
    {
        $this->generateUniqueFilename($file->getClientFilename() ?? '');

        $path = $this->getPath();
        if (file_exists($path)) {
            throw new Exception('A file already exist with the same name: ' . $this->getFilename());
        }
        $file->moveTo($path);

        $this->validateMimeType();
    }

    /**
     * Set filename (without path)
     *
     * @API\Exclude
     *
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * Get filename (without path)
     *
     * @API\Exclude
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Get mime
     *
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * Get absolute path to file on disk
     *
     * @API\Exclude
     *
     * @return string
     */
    public function getPath(): string
    {
        return realpath('.') . '/' . $this->getBasePath() . $this->getFilename();
    }

    /**
     * Automatically called by Doctrine when the object is deleted
     * Is called after database update because we can have issues on remove operation (like integrity test)
     * and it's preferable to keep a related file on drive before removing it definitely.
     *
     * @ORM\PostRemove
     */
    public function deleteFile(): void
    {
        $path = $this->getPath();
        if (file_exists($path) && is_file($path) && mb_strpos($this->getFilename(), 'dw4jV3zYSPsqE2CB8BcP8ABD0.') === false) {
            unlink($path);
        }
    }

    /**
     * Generate unique filename while trying to preserve original extension
     *
     * @param string $originalFilename
     */
    private function generateUniqueFilename(string $originalFilename): void
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $filename = uniqid() . ($extension ? '.' . $extension : '');
        $this->setFilename($filename);
    }

    /**
     * Delete file and throw exception if MIME type is invalid
     */
    private function validateMimeType(): void
    {
        $path = $this->getPath();
        $mime = mime_content_type($path);
        if ($mime === false) {
            throw new Exception('Could not get mimetype for path: ' . $path);
        }

        if ($mime === 'image/svg') {
            $mime = 'image/svg+xml';
        }

        // Validate mimetype
        if (!in_array($mime, $this->getAcceptedMimeTypes(), true)) {
            unlink($path);

            throw new Exception('Invalid file type of: ' . $mime);
        }

        $this->mime = $mime;
    }
}
