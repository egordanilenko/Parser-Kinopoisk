<?php

namespace Eadanilenko\KinopoiskInfo;


class Trailer
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var array| TrailerItem[]
     */
    public $sd = array();

    /**
     * @var array| TrailerItem[]
     */
    public $hd = array();
}