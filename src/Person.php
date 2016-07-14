<?php

namespace Eadanilenko\KinopoiskInfo;


class Person
{
    /**
     * @var int;
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * Person constructor.
     * @param int $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id = (int)$id;
        $this->name = $name;
    }
}