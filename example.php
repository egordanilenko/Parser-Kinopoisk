<?php
use Eadanilenko\KinopoiskInfo\KinopoiskInfo;
header('Content-Type: application/json');
$id = $_GET['id'];
include_once ('vendor/autoload.php');

$memcached = new \Memcached();
$memcached->addServer('127.0.0.1',11211);
try{
    $kinopoisk = new KinopoiskInfo($memcached,1,'dimmduh','gfhjkm03');

    echo json_encode($kinopoisk->getMovieFromId($id));

}catch (\Eadanilenko\KinopoiskInfo\MovieNotFoundException $e){
    echo json_encode((object)array('code' => $e->getCode(),'message' => $e->getMessage()));
}
