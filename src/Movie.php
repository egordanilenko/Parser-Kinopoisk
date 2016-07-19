<?php
/**
 * Created by PhpStorm.
 * User: egor
 * Date: 14.07.16
 * Time: 23:38
 */

namespace Eadanilenko\KinopoiskInfo;


class Movie
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $originalName;

    /**
     * @var int
     */
    public $year;

    /**
     * @var string
     */
    public $countryTitle;

    /**
     * @var string
     */
    public $slogan;

    /**
     * @var array|Person[]
     */
    public $actors=array();

    /**
     * @var array|Person[]
     */
    public $director=array();

    /**
     * @var array|Person[]
     */
    public $script=array();

    /**
     * @var array|Person[]
     */
    public $producer=array();

    /**
     * @var array|Person[]
     */
    public $operator=array();

    /**
     * @var array|Person[]
     */
    public $composer=array();

    /**
     * @var array|Genre[]
     */
    public $genre=array();

    /**
     * @var string
     * TODO: convert to int
     */
    public $rusCharges;

    /**
     * @var string
     * TODO: convert to DateTime
     *
     */
    public $worldPremiere;

    /**
     * @var string
     * TODO: convert to DateTime
     *
     */
    public $rusPremiere;

    /**
     * @var int
     * TODO: convert to DateInterval or int
     *
     */
    public $duration;

    /**
     * @var string
     * TODO: convert to double (pass total votes)
     */
    public $imdbRating;

    /**
     * @var string
     * TODO: convert to double
     */
    public $rating;

    /**
     * @var string
     */
    public $posterUrl;

    /**
     * @var string
     */
    public $trailerUrl;

    /**
     * @var array|Trailer[]
     */
    public $trailers = array();

    /**
     * @var string
     */
    public $description;
    
}