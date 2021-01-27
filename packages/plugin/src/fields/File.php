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

    public function settingsAttributes(): array
    {
        return array_merge(
            parent::settingsAttributes(),
            ['maxFileSizeKB', 'restrictFileKinds', 'fileKinds', 'fileCount', 'volumeId']
        );
    }

    public function getType(): string
    {
        return self::TYPE_FILE;
    }

    public function getMaxFileSizeKB(): int
    {
        return $this->maxFileSizeKB;
    }

    /**
     * @param int $maxFileSizeKB
     */
    public function setMaxFileSizeKB(int $maxFileSizeKB = null): self
    {
        $this->maxFileSizeKB = $maxFileSizeKB;

        return $this;
    }

    public function isRestrictFileKinds(): bool
    {
        return $this->restrictFileKinds;
    }

    /**
     * An alias for ::isRestrictFileKinds().
     */
    public function getRestrictFileKinds(): bool
    {
        return $this->isRestrictFileKinds();
    }

    /**
     * @param bool $restrictFileKinds
     */
    public function setRestrictFileKinds(bool $restrictFileKinds = null): self
    {
        $this->restrictFileKinds = $restrictFileKinds ?? false;

        return $this;
    }

    public function getFileKinds(): array
    {
        return $this->fileKinds;
    }

    /**
     * @param array $fileKinds
     */
    public function setFileKinds(array $fileKinds = null): self
    {
        $this->fileKinds = $fileKinds;

        return $this;
    }

    public function getFileCount(): int
    {
        return $this->fileCount;
    }

    /**
     * @param int $fileCount
     */
    public function setFileCount(int $fileCount = null): self
    {
        $this->fileCount = $fileCount;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getVolumeId()
    {
        return $this->volumeId;
    }

    /**
     * @param int $volumeId
     */
    public function setVolumeId(int $volumeId = null): self
    {
        $this->volumeId = $volumeId;

        return $this;
    }
}
