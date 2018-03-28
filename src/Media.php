<?php

namespace Transitive\Utils;

class Media extends Model implements \JsonSerializable
{
    use Named;

    public static $path = 'data/media';

    private static $types = ['image', 'sound', 'video'];
    private static $sizes = [
        0 => 'small',
        1 => 'medium',
        2 => 'large',
    ];

    public const small = 0;
    public const medium = 1;
    public const large = 2;

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

    /**
     * @var int
     */
    private $maxSize;

/*
    public static function editable(self $media = null, array $options = []): string
    {
        static $editableId = 0;

        if(empty($media))
            $media = new self();

        $name = $options['name'] ?? 'media'.$editableId;
        $deletable = $options['deletable'] ?? true;

        $str = '<figure class="media editable" title="'.($media->getTitle() ?? 'Ajouter un média').'">';

        if($deletable)
            $str .= '<input type="checkbox" name="delete_'.($name).'" id="delete_'.($name).'" value="'.$media->getId().'" /><label for="delete_'.($name).'">Supprimer</label>';

        $str .= '<label for="mediaInput'.$editableId.'" class="uploadButton">Téléverser</label>';
        $str .= '<input type="file" id="mediaInput'.$editableId.'" name="mediaUpload" />';
        $str .= '<input type="hidden" name="'.($name).'" disabled="disabled" />';
        if($media->id > 0)
            $str .= '<img src="'.self::$path.'/'.$media->getMaxSize().'/'.$media->getId().'.'.$media->getExtension().'" alt="" />';
        $str .= '<figcaption>'.($media->getName() ?? '').'</figcaption>';
        $str .= '</figure>';
        ++$editableId;

        return $str;
    }
*/

    public static function editable(self $media = null, array $options = []): string
    {
        static $editableId = 0;

        if(empty($media))
            $media = new self();

        $name = $options['name'] ?? 'media';
        $className = $options['className'] ?? '';
        $deletable = $options['deletable'] ?? true;
        $maxSize = $options['maxSize'] ?? self::large;

        $str = '<figure class="media editable'.(($className) ? ' '.$className : '').'" title="'.($media->getTitle() ?? 'Ajouter un média').'">';

        $str .= '<input type="hidden" name="'.$name.'['.$editableId.'][id]" readonly value="'.$media->getId().'" />';
        if($deletable) {
            $script = "this.parentNode.querySelector('input[name$=\'[id]\']').value = '-1'; this.parentNode.querySelector('img').style.opacity = 0;";
            $str .= '<button type="button" id="delete_'.$name.$editableId.'" class="action remove" onclick="'.$script.'"><span>Supprimer</span></button>';
        }
        $str .= '<input type="hidden" name="'.$name.'['.$editableId.'][name]" value="'.($media->getName() ?? '').'" />';

        $str .= '<label for="mediaInput'.$editableId.'" class="action upload">Téléverser</label>';
        $str .= '<input type="file" id="mediaInput'.$editableId.'" name="mediaUpload" />';
        if($media->id > 0)
            $str .= $media->asImgElement($maxSize);
        $str .= '<figcaption>'.($media->getName() ?? '').'</figcaption>';
        $str .= '</figure>';
        ++$editableId;

        return $str;
    }

    public static function aslistItem(self $media = null, array $options = []): string
    {
        static $editableId = 0;

        if(empty($media))
            $media = new self();

        $name = $options['name'] ?? 'media';
        $deletable = $options['deletable'] ?? true;
        $maxSize = $options['maxSize'] ?? self::large;

        $str = '<li><h2>'.$media->getId().'</h2>';

        if($deletable)
            $str .= '<button type="button" class="action remove" onclick="removeElement(this)"><span>Supprimer</span></button>';

        $str .= '<input type="hidden" name="'.$name.'['.$editableId.'][id]" readonly value="'.$media->getId().'" />';
        $str .= '<input type="hidden" name="'.$name.'['.$editableId.'][name]" value="'.($media->getName() ?? '').'" />';
        if($media->id > 0)
            $str .= $media->asImgElement($maxSize);
        $str .= '</li>';
        ++$editableId;

        return $str;
    }

    public static function list(array $medias, array $options = []): string
    {
        $str = '<ul class="medias">';

        foreach($medias as $media)
            $str .= self::asListItem($media, $options);

        return $str.'</ul>';
    }

    public function __construct($type = 'image', $mimeType = 'image/jpeg', $extension = 'jpg', int $maxSize = self::small, $name = null, $title = null)
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

    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    public function getMaxSizeString(): string
    {
        return self::$sizes[$this->maxSize];
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

    public function setMaxSize(int $maxSize): void
    {
        if(!isset(self::$sizes[$maxSize]))
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

    public function asImgElement(int $maxSize = null): string
    {
        if(isset($maxSize) && $maxSize < $this->getMaxSize())
            $size = self::$sizes[$maxSize];
        else
            $size = $this->getMaxSizeString();

        $str = '';
        if($this->id > 0)
            $str .= '<img src="'.self::$path.'/'.$size.'/'.$this->getId().'.'.$this->getExtension().'" alt="" />';

        return $str;
    }

    public function __toString()
    {
        $str = '<figure title="'.$this->getTitle().'" class="media">';
        $str .= $this->asImgElement();
        $str .= '<figcaption>'.$this->getName().'</figcaption>';
        $str .= '</figure>';

        return $str;
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            'type' => htmlentities($this->getType()),
            'mime' => htmlentities($this->getMimeType()),
            'extension' => htmlentities($this->getExtension()),
            'maxSize' => htmlentities($this->getMaxSize()),
            'maxSizeString' => htmlentities($this->getMaxSizeString()),
            'path' => htmlentities(self::$path),
        ] + $this->_namedJsonSerialize();
    }

    public static function ImgElement(?self $media = null, int $maxSize = null): string
    {
        if($media)
            return $media->asImgElement($maxSize);

        return '';
    }
}
