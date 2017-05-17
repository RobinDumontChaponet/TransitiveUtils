<?php


namespace Transitive\Utils;
use \Datetime;

trait Dated
{
    /**
     * @var DateTime
     */
    protected $cTime;

    /**
     * @var DateTime
     */
    protected $mTime;

    /**
     * @var DateTime
     */
    protected $aTime;

    protected function _initDated(DateTime $time = null)
    {
        $this->cTime = $time ?? new DateTime();
        $this->aTime = $time ?? new DateTime();
    }

    public function getCreationTime(): DateTime
    {
        return $this->cTime;
    }

    public function getModificationTime(): ?DateTime
    {
        return $this->mTime;
    }

    public function getAccessTime(): ?DateTime
    {
        return $this->aTime;
    }

    public function setCreationTime(DateTime $cTime): void
    {
        $this->cTime = $cTime;
    }

    public function setModificationTime(DateTime $mTime = null): void
    {
        $this->mTime = $mTime;
    }

    public function setAccessTime(DateTime $aTime = null): void
    {
        $this->aTime = $aTime;
    }
}
