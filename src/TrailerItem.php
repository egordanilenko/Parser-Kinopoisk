<?php

namespace Eadanilenko\KinopoiskInfo;


class TrailerItem
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $quality;

    /**
     * TrailerItem constructor.
     * @param $url
     * @param $quality
     */
    public function __construct($url, $quality)
    {
        $this->url = $url;
        $this->quality = $quality;
    }

}