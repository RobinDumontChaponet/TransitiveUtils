<?php

namespace Transitive\Utils;

trait Named
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $title;

    protected function _initNamed(string $name, string $title = null)
    {
        $this->name = $name;
        $this->title = $title;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title ?? '';
    }

    public function hasName(): bool
    {
        return !empty($this->name);
    }

    public function hasTitle(): bool
    {
        return !empty($this->title);
    }

    public function setName(string $name): void
    {
        $e = null;
        $name = trim($name);

        if(empty($name))
            $e = new ModelException('Le nom doit être renseigné.', null, $e);

        if(strlen($name) > 128)
            $e = new ModelException('Le nom doit être au maximum de 128 caractères.', null, $e);

        ModelException::throw($e);

        $this->name = $name;
    }

    public function setTitle(string $title = null): void
    {
        $e = null;
        $title = trim($title);

        if(is_numeric($title))
            $e = new ModelException('Le sous-titre ne peut pas être constitué de chiffres seulement.', null, $e);

        if(strlen($title) > 128)
            $e = new ModelException('Le sous-titre doit être de maximum 128 caractères.', null, $e);

        ModelException::throw($e);

        $this->title = $title;
    }

    protected function _namedJsonSerialize(): array
    {
        $array = [
            'name' => htmlentities($this->getName()),
        ];
        if(isset($this->title))
            $array['title'] = htmlentities($this->getTitle());

        return $array;
    }
}
