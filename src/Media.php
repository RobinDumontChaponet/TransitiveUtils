<?php

namespace Transitive\Utils;

class Media extends Model implements \JsonSerializable
{
    use Named;

    public static $path = 'data/media';

    private static $types = ['image', 'sound', 'video'];
    private static $sizes = ['small', 'medium', 'large'];

    /**
     * @var enum
     */
    private $type;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var string
     */
    private $extension;

    private $maxSize;

    private static $editableId = 0;

    public static function editable(Media $media = null, string $name = null): string
    {
        if(empty($media))
            $media = new self();

        $str = '<figure class="media editable auto-init" title="'.($media->getTitle() ?? 'Ajouter un média').'">';
        $str .= '<label for="mediaInput'.self::$editableId.'">Téléverser</label>';
        $str .= '<input type="file" id="mediaInput'.self::$editableId.'" name="mediaUpload" />';
        $str .= '<input type="hidden" name="'.($name ?? 'media'.self::$editableId).'" disabled="disabled" />';
        if($media->id > 0)
            $str .= '<img src="'.self::$path.'/'.$media->getMaxSize().'/'.$media->getId().'.'.$media->getExtension().'" alt="" />';
        $str .= '<figcaption>'.($media->getName() ?? '').'</figcaption>';
        $str .= '</figure>';
        ++self::$editableId;

        return $str;
    }

    public function __construct($type = 'image', $mimeType = 'image/jpeg', $extension = 'jpg', $maxSize = 'small', $name = null, $title = null)
    {
        parent::__construct();

        $this->_initNamed($name ?? '', $title);

        $this->setType($type);
        $this->setMimeType($mimeType);
        $this->setExtension($extension);
        $this->setMaxSize($maxSize);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getMaxSize(): string
    {
        return $this->maxSize;
    }

    public function setType(string $type): void
    {
        if(!in_array($type, self::$types))
            throw new \Exception('Invalid type : '.$type);
        $this->type = $type;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    public function setMaxSize(string $maxSize): void
    {
        if(!in_array($maxSize, self::$sizes))
            throw new \Exception('Invalid size');
        $this->maxSize = $maxSize;
    }

    public function setName(string $name): void
    {
        $name = trim($name);

        if(strlen($name) > 40)
            throw new ModelException('Le nom doit être au maximum de 40 caractères.', null, $e);

        $this->name = $name;
    }

	public function asImgElement(): string
    {
        $str = '';
        if($this->id > 0)
            $str .= '<img src="'.self::$path.'/'.$this->getMaxSize().'/'.$this->getId().'.'.$this->getExtension().'" alt="" />';

        return $str;
    }

    public function __toString()
    {
        $str = '<figure title="'.$this->getTitle().'">';
        $str.= $this->asImgElement();
        $str.= '<figcaption>'.$this->getName().'</figcaption>';
        $str.= '</figure>';

        return $str;
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            'type' => htmlentities($this->getType()),
            'mime' => htmlentities($this->getMimeType()),
            'extension' => htmlentities($this->getExtension()),
            'maxSize' => htmlentities($this->getMaxSize()),
            'path' => htmlentities(self::$path),
        ] + $this->_namedJsonSerialize();
    }

    public static function ImgElement(?Media $media = null): string
    {
	    if($media)
		    return $media->asImgElement();

		return '';
    }
}
