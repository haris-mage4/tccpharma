<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Answer;

use Amasty\Customform\Api\Answer\AttachedFileDataProviderInterface;
use Amasty\Customform\Api\Answer\GetAttachedFileUrlInterface;
use Amasty\Customform\Exceptions\AttachedFileDoesNotExistsException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as FileDriver;
use Magento\MediaStorage\Model\File\Uploader;

class AttachedFileDataProvider implements AttachedFileDataProviderInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var GetAttachedFileUrlInterface
     */
    private $getAttachedFileUrl;

    /**
     * @var FileDriver
     */
    private $fileDriver;

    public function __construct(
        Filesystem $filesystem,
        GetAttachedFileUrlInterface $getAttachedFileUrl,
        FileDriver $fileDriver
    ) {
        $this->filesystem = $filesystem;
        $this->getAttachedFileUrl = $getAttachedFileUrl;
        $this->fileDriver = $fileDriver;
    }

    public function getPath(string $fileName): string
    {
        $mediaDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();

        return sprintf(
            '%s%s%s%s%s',
            rtrim($mediaDir, DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            GetAttachedFileUrl::AMASTY_CUSTOMFORM_MEDIA_PATH,
            DIRECTORY_SEPARATOR,
            Uploader::getCorrectFileName($fileName)
        );
    }

    public function getContents(string $fileName): string
    {
        $filePath = $this->getPath($fileName);

        if ($this->fileDriver->fileExists($filePath)) {
            return $this->fileDriver->read($filePath);
        } else {
            throw AttachedFileDoesNotExistsException::forFile($fileName);
        }
    }

    public function getUrl(string $fileName, ?int $storeId = null): string
    {
        return $this->getAttachedFileUrl->execute($fileName, $storeId);
    }
}
