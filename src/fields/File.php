<?php

namespace Solspace\ExpressForms\fields;

class File extends MultipleValueField
{
    /** @var int */
    private $maxFileSizeKB = 2000;

    /** @var bool */
    private $restrictFileKinds = true;

    /** @var array */
    private $fileKinds = [];

    /** @var int */
    private $fileCount = 1;

    /** @var int */
    private $volumeId;

    /**
     * @return array
     */
    public function settingsAttributes(): array
    {
        return array_merge(
            parent::settingsAttributes(),
            ['maxFileSizeKB', 'restrictFileKinds', 'fileKinds', 'fileCount', 'volumeId']
        );
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_FILE;
    }

    /**
     * @return int
     */
    public function getMaxFileSizeKB(): int
    {
        return $this->maxFileSizeKB;
    }

    /**
     * @param int $maxFileSizeKB
     *
     * @return File
     */
    public function setMaxFileSizeKB(int $maxFileSizeKB = null): File
    {
        $this->maxFileSizeKB = $maxFileSizeKB;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRestrictFileKinds(): bool
    {
        return $this->restrictFileKinds;
    }

    /**
     * An alias for ::isRestrictFileKinds()
     *
     * @return bool
     */
    public function getRestrictFileKinds(): bool
    {
        return $this->isRestrictFileKinds();
    }

    /**
     * @param bool $restrictFileKinds
     *
     * @return File
     */
    public function setRestrictFileKinds(bool $restrictFileKinds = null): File
    {
        $this->restrictFileKinds = $restrictFileKinds ?? false;

        return $this;
    }

    /**
     * @return array
     */
    public function getFileKinds(): array
    {
        return $this->fileKinds;
    }

    /**
     * @param array $fileKinds
     *
     * @return File
     */
    public function setFileKinds(array $fileKinds = null): File
    {
        $this->fileKinds = $fileKinds;

        return $this;
    }

    /**
     * @return int
     */
    public function getFileCount(): int
    {
        return $this->fileCount;
    }

    /**
     * @param int $fileCount
     *
     * @return File
     */
    public function setFileCount(int $fileCount = null): File
    {
        $this->fileCount = $fileCount;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getVolumeId()
    {
        return $this->volumeId;
    }

    /**
     * @param int $volumeId
     *
     * @return File
     */
    public function setVolumeId(int $volumeId = null): File
    {
        $this->volumeId = $volumeId;

        return $this;
    }
}
