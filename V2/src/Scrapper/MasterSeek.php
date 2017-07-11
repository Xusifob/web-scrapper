<?php

namespace Extractor\Scrapper;


include __DIR__ . '/../header.php';


use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Scrapper
 *
 * Class MasterSeek
 *
 */
class MasterSeek extends Scrapper implements ScrapperInterface
{


    /** @inheritdoc */
    protected $url = 'http://www.masterseek.com/Companies/France/Paris/Paris/business-services';


    /**
     * The export directory
     *
     * @var string
     */
    protected $dir = 'MasterSeek';




    /** @inheritdoc */
    public function parse()
    {


        $urls = json_decode(file_get_contents(MAIN_DIR . '/data/mastermind.json',true),true);

        foreach($urls as $key => $url)
        {
            if(preg_match('/RedirectPage.aspx|Result.aspx/',$url)){
                unset($urls[$key]);
            }
        }

        foreach($urls as $url)
        {
            $this->scrapFiche($url);

        }


    }




    /** @inheritdoc */
    public function scrapFiche($url)
    {

        $response = $this->getHtml($url);


        if($response instanceof JsonResponse){
            return;
        }



        $company  = array();

        $company['company']  = $response->find('#lblCompantName')[0]->innertext;
        $company['description']  = $response->find('#lblDescription')[0]->innertext;
        $company['phone']  = $response->find('#lblPhone')[0]->innertext;
        $company['email']  = $response->find('#lblEmail')[0]->innertext;
        $company['website']  = $response->find('#lblWebsite')[0]->innertext;
        $company['contact']  = $response->find('#lblContactPerson')[0]->innertext;
        $company['turnover']  = $response->find('#lblTurnover')[0]->innertext;
        $company['address']  = str_replace('<br>',' ',$response->find('#lblAddress')[0]->innertext);



        $this->save($company);

    }


}



$scrapper = new MasterSeek();

$scrapper->parse();
$scrapper->export();