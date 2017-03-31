<?php

namespace Transitive\Utils;

trait Dated
{
    /**
     * @var int
     */
    protected $cTime;

    /**
     * @var int
     */
    protected $mTime;

    /**
     * @var int
     */
    protected $aTime;

    protected function _init(int $time = null)
    {
        $this->cTime = $time ?? time();
        $this->aTime = $time ?? time();
    }

    public function getCreationTime(): int
    {
        return $this->cTime;
    }

    public function getModificationTime(): ?int
    {
        return $this->mTime;
    }

    public function getAccessTime(): ?int
    {
        return $this->aTime;
    }

    public function setCreationTime(int $cTime): void
    {
        $this->cTime = $cTime;
    }

    public function setModificationTime(int $mTime = null): void
    {
        $this->mTime = $mTime;
    }

    public function setAccessTime(int $aTime = null): void
    {
        $this->aTime = $aTime;
    }
}
