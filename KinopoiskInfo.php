<?php

class KinopoiskInfo{

    /**
     * @var Snoopy
     */
    private $snoopy;

    /**
     * @var Memcached
     */
    private $memcached;

    private $auth = array();

    const CLIENT_AGENT = "Mozilla/5.0 (Windows; U; Windows NT 6.1; uk; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13 Some plugins";

    const MEMECACHED_FILM_PREFIX = 'kinopoiks_info_film_';

    public function __construct(Memcached $memcached, $kinopoiskLogin=null, $kinopoiskPass=null)
    {
        $this->memcached = $memcached;
        $this->snoopy = new Snoopy();
        $this->snoopy->maxredirs = 2;

        if($kinopoiskLogin && $kinopoiskPass) $this->auth = array(
            'shop_user[login]' => $kinopoiskLogin,
            'shop_user[pass]' => $kinopoiskPass,
            'shop_user[mem]' => 'on',
            'auth' => 'go',
        );

        $this->snoopy->agent = self::CLIENT_AGENT;
    }

    /**
     * @param $id int
     * @return  string
     */
    public function getFilmMetaFromId($id){

        if($content = $this->memcached->get(self::MEMECACHED_FILM_PREFIX.$id)){
            return $content;
        }
        if(count($this->auth)>0){
            $this->snoopy->submit('http://www.kinopoisk.ru/level/30/', $this->auth);
            $this->snoopy->fetch('http://www.kinopoisk.ru/film/'.$id);
        }


        $main_page = $this->snoopy -> results;
        $main_page = iconv('windows-1251' , 'utf-8', $main_page);

        // страница с трейлерами
        $this->snoopy -> fetch('http://www.kinopoisk.ru/film/' . $id . '/video/type/1/');
        $trailers_page = $this->snoopy -> results;
        $trailers_page = iconv('windows-1251' , 'utf-8', $trailers_page);

        $parse = array(
            'name' =>         '#<h1.*?class="moviename-big".*?>(.*?)</h1>#si',
            'originalname'=>  '#<span itemprop="alternativeHeadline">(.*?)</span>#si',
            'year' =>         '#год</td>.*?<a[^>]*>(.*?)</a>#si',
            'country_title' =>'#страна</td>.*?<td[^>]*>(.*?)</td>#si',
            'slogan' =>       '#слоган</td><td[^>]*>(.*?)</td></tr>#si',
            'actors_main' =>  '#В главных ролях:</h4>[^<]*<ul>(.*?)</ul>#si',
            'actors_voices' =>'#Роли дублировали:</h4>[^<]*<ul>(.*?)</ul>#si',
            'director' =>     '#режиссер</td><td[^>]*>(.*?)</td></tr>#si',
            'script' =>       '#сценарий</td><td[^>]*>(.*?)</td></tr>#si',
            'producer' =>     '#продюсер</td><td[^>]*>(.*?)</td></tr>#si',
            'operator' =>     '#оператор</td><td[^>]*>(.*?)</td></tr>#si',
            'composer' =>     '#композитор</td><td[^>]*>(.*?)</td></tr>#si',
            'genre' =>        '#жанр</td><td[^>]*>[^<]*<span[^>]*>(.*?)</span>#si',
            'budget' =>       '#бюджет</td>.*?<a href="/level/85/film/[0-9]+/" title="">(.*?)</a>#si',
            'usa_charges' =>  '#сборы в США</td>.*?<a href="/level/85/film/[0-9]+/" title="">(.*?)</a>#si',
            'world_charges' =>'#сборы в мире</td>.*?<a href="/level/85/film/[0-9]+/" title="">(.*?)</a>#si',
            'rus_charges' =>  '#сборы в России</td>.*?<div style="position: relative">(.*?)</div>#si',
            'world_premiere'=>'#премьера \(мир\)</td>[^<]*<td[^>]*>.*?<a[^>]*>(.*?)</a>#si',
            'rus_premiere' => '#премьера \(РФ\)</td>[^<]*<td[^>]*>.*?<a[^>]*>(.*?)</a>#si',
            'time' =>         '#id="runtime">(.*?)</td></tr>#si',
            'description' =>  '#<span class=\"_reachbanner_\"><div class=\"brand_words\"[^>]*>(.*?)</div></span>#si',
            'imdb' =>         '#IMDB:\s(.*?)</div>#si',
            'kinopoisk' =>    '#<div id="block_rating".*?<span class="rating_ball">(.*?)</span>#si',
            'kp_votes' =>     '#<span style=\"font:100 14px tahoma, verdana\">(.*?)</span>#si',
        );

        $trailers_parse = array(
            'url' =>     '#<a href="/getlink\.php[^"]*?link=([^"]*)" class="continue">(.*?)</a>#si',
            'trailer_page' => '#<a href="([^"]*)" class="all"#si',
            'html'	=> '#<!-- ролик -->([\w\W]*?)<!-- \/ролик -->#si'
        );


        $new=array();

        foreach($parse as $index => $value){
            if (preg_match($value,$main_page,$matches)) {
                if (in_array($index, array('actors_voices','actors_main'))) { // здесь нужен дополнительный парсинг
                    if (preg_match_all('#<li itemprop="actors"><a href="/name/(\d+)/">(.*?)</a></li>#si',$matches[1],$matches2,PREG_SET_ORDER)) {
                        $new[$index] = array();
                        foreach ($matches2 as $match) {
                            if (strip_tags($match[2]) != '...') $new[$index][] = array('name'=>strip_tags($match[2]),'id'=>$match[1]);
                        }
                    }
                } else if (in_array($index, array(
                    'director',
                    'script',
                    'producer',
                    'operator',
                    'composer',
                ))) {
                    if (preg_match_all('#<a href="/name/(\d+)/">(.*?)</a>#si',$matches[1],$matches2,PREG_SET_ORDER)) {
                        $new[$index] = array();
                        foreach ($matches2 as $match) {
                            if (strip_tags($match[2]) != '...') $new[$index][] = array('name'=>strip_tags($match[2]),'id'=>$match[1]);
                        }
                    }
                } else if ($index == 'genre') {
                    if (preg_match_all('#<a href="/lists/.*?/(\d+)/">(.*?)</a>#si',$matches[1],$matches2,PREG_SET_ORDER)) {
                        $new[$index] = array();
                        foreach ($matches2 as $match) {
                            if (strip_tags($match[2]) != '...') $new[$index][] = array('title'=>strip_tags($match[2]),'id'=>$match[1]);
                        }
                    }
                } else if ($index == 'poster_url') {
                    $new[ $index ] = 'http://www.kinopoisk.ru' . $matches[1];
                } else {
                    $new[ $index ] = preg_replace('#\\n\s*#si', '', html_entity_decode(strip_tags($matches[1]),ENT_COMPAT | ENT_HTML401, 'UTF-8'));
                    $new[ $index ] = $this->resultClear( $new[ $index ], $index );
                }
            }
        }

        $new['poster_url'] = 'http://www.kinopoisk.ru/images/film_big/' . $id . '.jpg';

        $url = array();
        $trailer_page = array();
        $all_trailers = array();

        foreach($trailers_parse as $index => $regex){
            if ($index == 'html') {
                if (preg_match_all($regex, $trailers_page, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) { // по всем трейлерам (в каждом по нескольку видео в разном качестве)

                        if (preg_match('#<tr>[\w\W]*?<a href="[^"]*" class="all">(.*?)</a>\s*<table[\w\W]*?</table>[\w\W]*?<tr>[\w\W]*?<table[\w\W]*?</table>[\w\W]*?<tr>[\w\W]*?<table[\w\W]*?</td>\s*<td>([\w\W]*?)</table>[\w\W]*?<td[\w\W]*?<td>([\w\W]*?)</table>#si', $match[1], $title_sd_hd_matches)) { // название, стандартное качество и HD
                            $trailer_family = array();
                            $trailer_family['title'] = $title_sd_hd_matches[1];
                            // SD качество
                            $sd = array();
                            if (preg_match_all('#<a href="/getlink\.php[^"]*?link=([^"]*)" class="continue">(.*?)</a>#si', $title_sd_hd_matches[2], $single_videos, PREG_SET_ORDER)) {
                                foreach ($single_videos as $single_video){
                                    $sd[] = array(
                                        'url' => $single_video[1],
                                        'quality' => strip_tags($single_video[2])
                                    );
                                }
                            }
                            $trailer_family['sd'] = $sd;
                            // HD качество
                            $hd = array();
                            if (preg_match_all('#<a href="/getlink\.php[^"]*?link=([^"]*)" class="continue">(.*?)</a>#si', $title_sd_hd_matches[3], $single_videos, PREG_SET_ORDER)) {
                                foreach ($single_videos as $single_video) {
                                    $hd[] = array(
                                        'url' => $single_video[1],
                                        'quality' => strip_tags($single_video[2])
                                    );
                                }
                            }
                            $trailer_family['hd'] = $hd;
                            $all_trailers[] = $trailer_family;
                        }

                    }
                }
            } else if (preg_match_all($regex,$trailers_page,$matches,PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    ${$index}[] = $match[1];
                }
            }
        }

        // переходим по ссылке на страницу главного трейлера и качаем ссылки на видео оттуда
        $main_trailer_url = array();
        if (isset($trailer_page[0])) {
            $this->snoopy -> fetch('http://www.kinopoisk.ru' . $trailer_page[0]);
            $main_trailer_page = $this->snoopy -> results;
            $main_trailer_page = iconv('windows-1251' , 'utf-8', $main_trailer_page);
            file_put_contents('main_trailer_'.$id.'.html', $main_trailer_page );

            if (preg_match_all('#<a href="/getlink\.php[^"]*?link=([^"]*)" class="continue">(.*?)</a>#si',$main_trailer_page,$matches,PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $main_trailer_url[] = array('description'=>strip_tags($match[2]),'url'=>$match[1]);
                }
            }
        }

        $new['trailer_url'] = $main_trailer_url[count($main_trailer_url)-1]['url'];
        $new['trailers'] = $all_trailers;

        $json = json_encode(array(
                'movie' => $new,
            )
        );


        $test = $this->memcached->set(self::MEMECACHED_FILM_PREFIX.$id, $json, 3600);
        $test2 = $this->memcached->get(self::MEMECACHED_FILM_PREFIX.$id);

        return $json;

    }

    private function resultClear( $val, $key = '' ){
        if ( empty( $val ) || $val == '-' ){
            $val = '';
        } else {
            $pattern = array('&nbsp;', '&laquo;', '&raquo;');
            $pattern_replace = array(' ','','');
            $val = str_replace( $pattern, $pattern_replace, $val );
        }
        switch ($key) {
            case 'genre':
            case 'producer':
            case 'operator':
            case 'director':
            case 'script':
            case 'composer':
                $val = str_replace(', ...','', $val );
                break;
        }

        return $val;
    }
}
