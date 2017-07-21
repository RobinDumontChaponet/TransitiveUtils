<?php

namespace Transitive\Utils;

use Datetime;

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

    public function getModificationTime()
    {
        return $this->mTime;
    }

    public function getAccessTime()
    {
        return $this->aTime;
    }

    public function setCreationTime(DateTime $cTime) 
    {
        $this->cTime = $cTime;
    }

    public function setModificationTime(DateTime $mTime = null) 
    {
        $this->mTime = $mTime;
    }

    public function setAccessTime(DateTime $aTime = null) 
    {
        $this->aTime = $aTime;
    }

    protected function _datedJsonSerialize(): array
    {
        return [
            'cTime' => $this->getCreationTime()->getTimestamp(),
            'mTime' => ($this->getModificationTime()) ? $this->getModificationTime()->getTimestamp() : null,
            'aTime' => ($this->getAccessTime()) ? $this->getAccessTime()->getTimestamp() : null,
        ];
    }
}
