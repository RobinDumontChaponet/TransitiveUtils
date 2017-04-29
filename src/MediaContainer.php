<?php

namespace Transitive\Utils;

trait MediaContainer
{
    /**
     * @var Media
     */
    protected $media = null;

    /**
     * @var bool
     */
    protected $mediaLocked = false;

    public function setMedia(Media $media = null): bool
    {
        if($this->mediaLocked)
            return false;

        $this->media = $media;

        return true;
    }

    public function lockMedia(bool $lock = true): void
    {
        $this->mediaLocked = $lock;
    }

    public function hasMedia(): bool
    {
        return isset($this->media);
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function isMediaLocked(): bool
    {
        return $this->mediaLocked;
    }
}
