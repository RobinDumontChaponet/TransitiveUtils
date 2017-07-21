<?php

namespace Transitive\Utils;

trait CountryContainer
{
    /**
     * @var Country
     */
    protected $country = null;

    public function setCountry(Country $country = null) 
    {
        $this->country = $country;
    }

    public function hasCountry(): bool
    {
        return isset($this->country);
    }

    public function getCountry()
    {
        return $this->country;
    }

    protected function _countryContainerJsonSerialize(): array
    {
        return [
            'country' => $this->getCountry(),
        ];
    }
}
