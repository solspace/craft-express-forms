<?php

namespace Solspace\ExpressForms\fields;

class File extends MultipleValueField
{
    private int $maxFileSizeKB = 2000;
    private bool $restrictFileKinds = true;
    private array $fileKinds = [];
    private int $fileCount = 1;
    private ?int $volumeId = null;

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

    public function setMaxFileSizeKB(int $maxFileSizeKB = null): self
    {
        $this->maxFileSizeKB = $maxFileSizeKB;

        return $this;
    }

    public function isRestrictFileKinds(): bool
    {
        return $this->restrictFileKinds;
    }

    public function getRestrictFileKinds(): bool
    {
        return $this->isRestrictFileKinds();
    }

    public function setRestrictFileKinds(bool $restrictFileKinds = null): self
    {
        $this->restrictFileKinds = $restrictFileKinds ?? false;

        return $this;
    }

    public function getFileKinds(): array
    {
        return $this->fileKinds;
    }

    public function setFileKinds(array $fileKinds = null): self
    {
        $this->fileKinds = $fileKinds;

        return $this;
    }

    public function getFileCount(): int
    {
        return $this->fileCount;
    }

    public function setFileCount(int $fileCount = null): self
    {
        $this->fileCount = $fileCount;

        return $this;
    }

    public function getVolumeId(): ?int
    {
        return $this->volumeId;
    }

    public function setVolumeId(int $volumeId = null): self
    {
        $this->volumeId = $volumeId;

        return $this;
    }
}
