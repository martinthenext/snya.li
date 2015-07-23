<?php

/**
 * Создает сайтмап для всего сайта
 */
class SitemapCommand extends CConsoleCommand
{

    private $_sitemapDirectory;

    public function beforeAction($action, $params)
    {
        $this->_sitemapDirectory = "/var/www/snya.li/www/sitemaps/";

        $_SERVER = array(
            'HTTP_HOST' => 'snya.li',
            'SERVER_NAME' => 'snya.li',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SCRIPT_FILENAME' => '/index.php',
            'SCRIPT_NAME' => 'index.php',
            'HTTPS' => 'on',
        );

        Yii::app()->urlManager->baseUrl = (($_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'];

        if (!is_dir($this->_sitemapDirectory) || !is_writable($this->_sitemapDirectory)) {
            $this->Log("Папка {$this->_sitemapDirectory} не существует или недоступна для записи.");
            exit();
        }
        date_default_timezone_set("Europe/Moscow");
        return parent::beforeAction($action, $params);
    }

    public function actionIndex()
    {


        $urls = [
            [
                'url' => Yii::app()->createAbsoluteUrl('/'),
                'lastmod' => date("Y-m-d\TH:i:s+00:00"),
            ]
        ];

        $criteria = new CDbCriteria();
        $criteria->condition = "t.enabled = 1";
        $adverts = Adverts::model()->with(array('city'=>array('joinType'=>'inner join')))->findAll($criteria);

        foreach ($adverts as $advert) {
            $urls[] = [
                'url' => Yii::app()->createAbsoluteUrl('items/item', array(
                    'city' => $advert->city->link, 
                    'type' => $advert->type_data->link, 
                    'link' => $advert->link, 
                    'id' => $advert->id
                )),
                'lastmod' => date("Y-m-d\TH:i:s+00:00", $advert->created),
            ];
        }

        $sitemapList = array_chunk($urls, 50000);
        $sitemaps = [];

        foreach ($sitemapList as $id => $urls) {
            $this->_createSitemap($id, $urls);
            $sitemaps[] = [
                'loc' => Yii::app()->urlManager->baseUrl."/sitemaps/sitemap{$id}.xml.gz",
                'lastmod' => date("Y-m-d\TH:i:s+00:00"),
            ];
        }
        $this->_createSitemapIndex($sitemaps);
    }

    private function _createSitemap($id, $urls)
    {
        $filename = $this->_sitemapDirectory . "sitemap";
        $dom = new domDocument("1.0", "utf-8");
        $root = $dom->createElement("urlset");
        $root->setAttribute("xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9");
        foreach ($urls as $url) {
            echo $url['url'].PHP_EOL;
            $urlNode = $dom->createElement("url");
            $locNode = $dom->createElement("loc", $url['url']);
            if (!empty($url['lastmod'])) {
                $lastModNode = $dom->createElement("lastmod", $url['lastmod']);
                $urlNode->appendChild($lastModNode);
            }
            $urlNode->appendChild($locNode);
            $root->appendChild($urlNode);
        }
        $dom->appendChild($root);
        $dom->save($filename);
        try {
            $fp = gzopen("{$filename}{$id}.xml.gz", 'w');
            gzwrite($fp, file_get_contents($filename));
            gzclose($fp);
        } catch (Exception $ex) {
            throw $ex;
        }

        unlink($filename);
    }

    private function _createSitemapIndex($sitemaps)
    {
        $filename = $this->_sitemapDirectory . "sitemapindex.xml";
        $dom = new domDocument("1.0", "utf-8");
        $root = $dom->createElement("sitemapindex");
        $root->setAttribute("xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9");
        foreach ($sitemaps as $sitemap) {
            $sitemapNode = $dom->createElement("sitemap");
            $locNode = $dom->createElement("loc", $sitemap['loc']);
            $sitemapNode->appendChild($locNode);
            if (!empty($sitemap['lastmod'])) {
                $lastModNode = $dom->createElement("lastmod", $sitemap['lastmod']);
                $sitemapNode->appendChild($lastModNode);
            }
            $root->appendChild($sitemapNode);
        }
        $dom->appendChild($root);
        $dom->save($filename);
        echo $filename . PHP_EOL;
    }

}
