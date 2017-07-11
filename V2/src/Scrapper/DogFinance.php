<?php

namespace Extractor\Scrapper;



include_once __DIR__ . '/../header.php';

use Extractor\Service\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Scrapper
 *
 * Class Capital
 *
 */
class DogFinance extends Scrapper implements ScrapperInterface
{



    public $prefix  = 'http://www.dogfinance.com';


    // 356



    public function __construct()
    {

        parent::__construct();

    }


    /** @inheritdoc */
    public function parse()
    {

        return $this->scrapFiche($this->url);

    }




    /** @inheritdoc */
    public function scrapFiche($url)
    {

        $response = $this->do_request($url,array(
            'headers' => array(
                'Host' =>'www.dogfinance.com',
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
            )
        ));


        if($response instanceof JsonResponse){
            $response->send();
            return;
        }

        preg_match('/\/fr\/a\/profil\/banniere\/show_coord\/[0-9]+\//i',$response,$matches);


        if(!isset($matches[0])){
            die('No link found');
        }

        $url = trim($matches[0]);


        return $this->scrapFiche($url);

    }


    /**
     * @param $url
     * @return array
     */
    public function scrapOneFiche($url)
    {

        $response = $this->getHtml($this->prefix . $url,array(
            'connect_timeout' => 20,
            'read_timeout' => 20,
            'timeout' => 45,
            'headers' => array(
                'X-Requested-With' => 'XMLHttpRequest',
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
                'Host' => 'www.dogfinance.com',
            )
        ),false);

        $company  = array();

        if(!isset( $response->find('.vcard .given-name')[0])){
            return array();
        }


        $company['first_name'] = $response->find('.vcard .given-name')[0]->innertext;
        $company['last_name'] = $response->find('.vcard .family-name')[0]->innertext;
        $company['poosition'] = isset($response->find('.vcard .org')[0]) ? trim($response->find('.vcard .title')[0]->innertext) : $response->find('.vcard .vcard_desc')[0]->innertext;
        $company['company'] = isset($response->find('.vcard .org')[0]) ? $response->find('.vcard .org')[0]->innertext : '';
        preg_match('/INTERNET:[^@]+@[^.]+\.[a-z]{0,10}/i',$response->find('#qr_content_coords')[0]->innertext,$matches);


        if(isset($matches[0])){
            $company['email'] = preg_replace('/Internet:/i','',$matches[0]);
        }else{
            return array();
        }

        $company['dogfinance_ul'] = $this->url;
        $company['fiche_url'] = $url;

        $this->save($company);

        return $company;
    }


    /**
     * @param array $values
     * @return array
     */
    public function removeDuplicates($values = array())
    {
        $dir =  self::DIR . $this->dir .'/';


        $data = array();


        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $d = json_decode(file_get_contents($dir . '/' . $entry),true);
                    $data[] = ucfirst($d['first_name'] . ' ' .  $d['last_name']);
                    if(isset($d['dogfinance_ul'])){
                        $urls[] = $d['dogfinance_ul'];
                    }
                }
            }
            closedir($handle);
        }


        if(empty($values)){
            $was_empty = true;
            $values = json_decode(file_get_contents(MAIN_DIR . 'data/dogfinance.json'),true);
        }

        $u = array();


        foreach($values as $key => $value)
        {

            if(!in_array($this->prefix . $value['url'],$urls)){
                $u[] = $value;
            }



        }

        if(isset($was_empty)){
            file_put_contents(MAIN_DIR . 'data/dogfinance.json',json_encode($u,true));
        }

        return $u;

    }


    /**
     * @param $url
     * @return array
     */
    public function getProfileUrls($url)
    {
        $response = $this->getHtml($url,array(
            'connect_timeout' => 20,
            'read_timeout' => 20,
            'timeout' => 45,
            'headers' => array(
                'X-Requested-With' => 'XMLHttpRequest',
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
                'Host' => 'www.dogfinance.com',
            )
        ),false);



        $urls = array();

        foreach($response->find('.a_user') as $profile)
        {

            $urls[] = ['url' => $profile->find('a')[0]->getAttribute('href')];

        }

        return $urls;

    }



}

$scrapper = new DogFinance();

$scrapper->setUrl($scrapper->prefix);



if(!Utils::extractFromRequest('url')){
    return;
}


//$scrapper->export();