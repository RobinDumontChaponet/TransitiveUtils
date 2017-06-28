<?php

namespace Transitive\Utils;

class Country extends Model implements \JsonSerializable
{
    use Named;

    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $alpha2;

    /**
     * @var string
     */
    private $alpha3;

    public function __construct(string $name, int $code = null, string $alpha2 = null, string $alpha3 = null)
    {
        parent::__construct();

        $this->_initNamed($name ?? '');

        $this->setCode($code);
        $this->setAlpha2($alpha2);
        $this->setAlpha3($alpha3);
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function getAlpha2(): ?string
    {
        return $this->alpha2;
    }

    public function getAlpha3(): ?string
    {
        return $this->alpha3;
    }

    public function setCode(int $code = null): void
    {
        $this->code = $code;
    }
    public function setAlpha2(string $alpha2 = null): void
    {
        $this->alpha2 = $alpha2;
    }

    public function setAlpha3(string $alpha3 = null): void
    {
        $this->alpha3 = $alpha3;
    }

    public function __toString()
    {
        $str = '<span class="country">';
        $str.= $this->getName();
        $str.= '</span>';

        return $str;
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize()
        + $this->_namedJsonSerialize()
        + [
            'code' => htmlentities($this->getCode()),
            'alpha2' => htmlentities($this->getAlpha2()),
            'alpha3' => htmlentities($this->getAlpha3())
        ];
    }
}
