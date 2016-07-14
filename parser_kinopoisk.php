<?php
header('Content-Type: text/json; charset=utf-8');
include_once ('Snoopy.class.php');
include_once ('KinopoiskInfo.php');

$memcached = new Memcached();
$memcached->addServer('127.0.0.1',11211);
$kinopoiskInfo = new KinopoiskInfo($memcached,'dimmduh','gfhjkm03');

echo $kinopoiskInfo->getFilmMetaFromId(843789);
?>