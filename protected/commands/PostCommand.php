<?php

class PostCommand extends CConsoleCommand
{

    public function beforeAction($action, $params)
    {

        $_SERVER = array(
            'HTTP_HOST' => 'snya.li',
            'SERVER_NAME' => 'snya.li',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SCRIPT_FILENAME' => '/index.php',
            'SCRIPT_NAME' => 'index.php',
            'HTTPS' => 'on',
        );

        Yii::app()->urlManager->baseUrl = (($_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'];

        date_default_timezone_set("Europe/Moscow");
        return parent::beforeAction($action, $params);
    }

    public function actionIndex()
    {

        Yii::app()->urlManager->baseUrl = (($_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'];

        $options = [
            'app_id' => 4943537,
            'app_secret' => '9ZY39OBWAzQVrB88SFzu',
            'app_authkey' => 'b5c5cca893e16d19fa16c94f01e369d1d7d7d190ff0be6cc8531e788211315f075055e5bdc25428ef7663',
            'group_id' => 94983589,
            'photo_album_id' => false,
            'video_album_id' => 1,
        ];

        $post = [
            'message' => '',
            'images' => [],
            'url' => '',
        ];

        //$api = new VkApi($options['app_id'], $options['app_secret']); $api->authorize(); exit();

        $criteria = new CDbCriteria();
        $interval = 86400; // сутки
        $criteria->condition = "t.enabled and t.created >= UNIX_TIMESTAMP() - {$interval} and !t.export_vk_post_id";
        $criteria->limit = 1;
        $criteria->order = "t.relevance desc";

        $advert = Adverts::model()->with(array('city', 'type_data'))->find($criteria);

        if (!$advert) {
            $interval = $interval * 2;
            $criteria->condition = "t.enabled and t.created >= UNIX_TIMESTAMP() - {$interval} and !t.export_vk_post_id";
            $advert = Adverts::model()->find($criteria);
        }

        if (!$advert) {
            echo "Empty set. Done." . PHP_EOL;
            exit();
        }

        foreach ($advert->images as $image) {
            $post['images'][] = $image->getFilePath();
        }

        $tags[] = $advert->type_data->title;

        $tags[] = $advert->city->title;

        if (!empty($advert->metro->title)) {
            $tags[] = $advert->metro->title;
        }

        $tags = array_map(function($value) {
            $value = '#' . preg_replace('/\s+/isu', "_", trim(htmlspecialchars_decode($value)));
            $value = preg_replace("/[^\w\_#]+/isu", '', $value);
            $value = mb_strtolower($value, 'UTF-8');
            return $value;
        }, $tags);

        $tags = array_unique($tags);
        $tags = array_chunk($tags, 9);
        $tags = $tags[0];


        $post['message'] = implode(" ", $tags) . PHP_EOL;
        $text = preg_replace("/!+/isu", "!", $advert->text);
        $text = preg_replace("/\<br\>/isu", PHP_EOL, $text);
        $text = html_entity_decode(htmlspecialchars_decode($advert->text));
        $text = strip_tags($text);
        $text = preg_replace("/[\x{fe00}-\x{fe0f}]/u", '', $text);
        $post['message'] .= trim($text) . PHP_EOL;
        $post['message'] .= 'Контакты: ' . $advert->vk_owner_first_name . ' ' . $advert->vk_owner_last_name . ' ';
        foreach ($advert->contacts as $contact) {
            $post['message'] .= $contact->value . " ";
        }
        $post['url'] = Yii::app()->createAbsoluteUrl('items/item', array('city' => $advert->city->link, 'type' => $advert->type_data->link, 'link' => $advert->link, 'id' => $advert->id));

        $post['message'] .= PHP_EOL . 'Смотреть полностью: ' . $post['url'];

        if (!empty($post['message']) || !empty($post['images'])) {
            // Данные для API
            $api = new VkApi($options['app_id'], $options['app_secret'], $options['app_authkey']);


            $albums = $api->run('photos.getAlbums', array(
                'owner_id' => $options['group_id'] * -1,
            ));
            foreach ($albums as $album) {
                if ($album->size < 10000 && $album->can_upload) {
                    $options['photo_album_id'] = $album->aid;
                    break;
                }
            }
            
            if ($options['photo_album_id'] === false) {
                $result = $api->run('photos.createAlbum', array(
                    'title'=>'Фото',
                    'group_id'=>$options['group_id'],
                    'privacy_view'=>'all',
                    'privacy_comment'=>'all',
                    'upload_by_admins_only'=>1, 
                ));
                
                $options['photo_album_id'] = $result->id;
            }
            
            //$options['photo_album_id'];
            if (!empty($post['images'])) {
                $result = $api->run('photos.getUploadServer', array(
                    'group_id' => (int) $options['group_id'],
                    'album_id' => (int) $options['photo_album_id'],
                    'captcha_sid' => '518594979355',
                    'captcha_key' => 'h2cnms',
                ));

                $upload_url = $result->upload_url;
                $postData = [];
                foreach ($post['images'] as $key => $image) {
                    if (class_exists('CURLFile', false)) {
                        $postData['file' . ($key + 1)] = new CURLFile($image);
                    } else {
                        $postData['file' . ($key + 1)] = '@' . $image;
                    }
                }
                $upload_result = $api->upload($upload_url, $postData);

                //var_dump($upload_result);
                if (!empty($upload_result->photos_list)) {
                    // Сохраняем загруженную фотку в альбоме группы
                    $result = $api->run('photos.save', array(
                        'album_id' => $upload_result->aid,
                        'group_id' => $upload_result->gid,
                        'server' => $upload_result->server,
                        'photos_list' => $upload_result->photos_list,
                        'hash' => $upload_result->hash,
                        'caption' => $post['url'],
                    ));

                    $results = array($result);
                    foreach ($results as $result) {
                        foreach ($result as $uploaded) {
                            $attachments[] = 'photo' . $uploaded->owner_id . '_' . $uploaded->pid;
                        }
                    }
                }
            }

            $attachments[] = $post['url'];

            $postParams = array(
                'owner_id' => ($options['group_id']) * -1,
                'from_group' => 1,
                'message' => $post['message'],
                'services' => 'twitter',
                'attachments' => implode(",", $attachments),
                    //'captcha_sid'=>'604216209994',
                    //'captcha_key'=>'sp7p5v',
            );

            // Постим!
            sleep(1);
            $result = $api->run('wall.post', $postParams);
            var_dump($result);
            $advert->export_vk_post_id = $result->post_id;
            $advert->save();
        }
    }

    public function actionTweet()
    {
        // Постим с 7 утра до 23 вечера
        if (date("H") < 8 || date("H") > 22) {
            //    exit();
        }

        $_SERVER = array(
            'HTTP_HOST' => 'snya.li',
            'SERVER_NAME' => 'snya.li',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'HTTPS' => 'on',
        );

        Yii::app()->urlManager->baseUrl = (($_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'];

        $httpClient = new GuzzleHttp\Client(['defaults' => [
                'verify' => false
        ]]);

        $twitterPath = realpath(dirname(dirname(__FILE__)) . "/components/twitter/");

        include_once $twitterPath . "/Config.php";
        include_once $twitterPath . "/SignatureMethod.php";
        include_once $twitterPath . "/HmacSha1.php";
        include_once $twitterPath . "/Response.php";
        include_once $twitterPath . "/Consumer.php";
        include_once $twitterPath . "/Token.php";
        include_once $twitterPath . "/Request.php";
        include_once $twitterPath . "/Util.php";
        include_once $twitterPath . "/Util/JsonDecoder.php";
        include_once $twitterPath . "/TwitterOAuth.php";
        #############

        $options = [
            'CONSUMER_KEY' => 'QaZhOArqdhX2dTeDgREKLZU1z',
            'CONSUMER_SECRET' => 'p6M92Hy25Sny8eCfaQRFbFWQgo2LzoTWF6Jt81AcW8HEF0aXSg',
            'OAUTH_TOKEN' => '3369377620-afhAyx3mKvGIhy8I9CFZJzzkc49WL7AW8Vx6IgE',
            'OAUTH_SECRET' => '3rA5lmWj6soKsB8gj6NV4otGkg8MSrROiNOwKTDCNbdhS',
            'account' => 'snyali_snyali',
        ];

        $criteria = new CDbCriteria();
        $interval = 86400; // сутки
        $criteria->condition = "t.enabled and t.created >= UNIX_TIMESTAMP() - {$interval} and t.export_tweet_status_id = '0'";
        $criteria->limit = 1;
        $criteria->order = "t.relevance desc";

        $advert = Adverts::model()->with(array('city', 'type_data'))->find($criteria);

        if (!$advert) {
            $interval = $interval * 2;
            $criteria->condition = "t.enabled and t.created >= UNIX_TIMESTAMP() - {$interval} and t.export_tweet_status_id = '0'";
            $advert = Adverts::model()->find($criteria);
        }

        if (!$advert) {
            echo "Empty set. Done." . PHP_EOL;
            exit();
        }

        $connection = new Abraham\TwitterOAuth\TwitterOAuth($options['CONSUMER_KEY'], $options['CONSUMER_SECRET'], $options['OAUTH_TOKEN'], $options['OAUTH_SECRET']);

        $images = array();
        $medias = array();


        foreach ($advert->images as $image) {


            usleep(500);

            if (count($medias) > 3) {
                break;
            }

            $upload = $connection->upload("media/upload", array('media' => $image->getFilePath()));
            $medias[] = $upload->media_id;
        }

        $tags = [];

        $tags[] = $advert->type_data->title;

        $tags[] = $advert->city->title;

        if (!empty($advert->metro->title)) {
            $tags[] = $advert->metro->title;
        }

        $tags = array_map(function($value) {
            $value = '#' . preg_replace('/\s+/isu', "_", trim(htmlspecialchars_decode($value)));
            $value = preg_replace("/[^\w\_#]+/isu", '', $value);
            $value = mb_strtolower($value, 'UTF-8');
            return $value;
        }, $tags);

        $tags = array_unique($tags);
        $tags = array_chunk($tags, 9);
        $tags = $tags[0];

        $messageLimit = 117;

        if (!empty($medias)) {
            $messageLimit -= 23;
        }

        $message = '';

        $maxTags = 7;

        foreach ($tags as $tag) {

            if (mb_strlen($message . $tag, 'UTF-8') + 1 >= $messageLimit) {
                continue;
            }

            if ($maxTags < 0) {
                continue;
            }

            $maxTags--;
            $message .= $tag . ' ';
        }

        $text = trim(str_replace('<br>', PHP_EOL, html_entity_decode(htmlspecialchars_decode($advert->text)))) . PHP_EOL;

        if (mb_strlen($message, 'UTF-8') < $messageLimit - 2) {
            $message .= mb_substr($text, 0, $messageLimit - mb_strlen($message, 'UTF-8') - 1, 'UTF-8') . ' ';
        }

        echo "message:" . mb_strlen($message) . PHP_EOL;

        $message .= Yii::app()->createAbsoluteUrl('items/item', array('city' => $advert->city->link, 'type' => $advert->type_data->link, 'link' => $advert->link, 'id' => $advert->id));

        $params = array(
            "status" => $message,
        );

        if (!empty($medias)) {
            $params['media_ids'] = implode(",", $medias);
        }

        $status = $connection->post("statuses/update", $params);
        if (!empty($status->id)) {
            $advert->export_tweet_status_id = $status->id;
            $advert->save();
        }
        var_dump($params);
        var_dump($status);
    }

    public function actionOk()
    {

        $options = array(
            'client_id' => 1146255872,
            'application_key' => 'CBALHDFFEBABABABA',
            'client_secret' => '72C8ACABABE600141660AD43',
            'secret_session_key' => '729f045fe8717746ad390bcdbecaaf70',
            'access_token' => 'tkn1IlcqnvVHUmYvJ2HpomVMyaQdf7MrNTPZoTuxJNFxuYK3wSj55Rug6ZzX0KjVqQDFO0',
            'group_id' => 53607146914036,
            'album_id' => 53607151894772,
        );

        $criteria = new CDbCriteria();
        $interval = 86400; // сутки
        $criteria->condition = "t.enabled and t.created >= UNIX_TIMESTAMP() - {$interval} and t.ok_post_id = ''";
        $criteria->limit = 1;
        $criteria->order = "t.relevance desc";

        $advert = Adverts::model()->with(array('city', 'type_data'))->find($criteria);

        if (!$advert) {
            $interval = $interval * 2;
            $criteria->condition = "t.enabled and t.created >= UNIX_TIMESTAMP() - {$interval} and t.ok_post_id = ''";
            $advert = Adverts::model()->find($criteria);
        }

        if (!$advert) {
            echo "Empty set. Done." . PHP_EOL;
            exit();
        }

        $post = [
            'message' => '',
            'images' => [],
            'url' => '',
        ];

        foreach ($advert->images as $image) {

            $post['images'][] = $image->getFilePath();
        }

        $tags[] = $advert->type_data->title;

        $tags[] = $advert->city->title;

        if (!empty($advert->metro->title)) {
            $tags[] = $advert->metro->title;
        }

        $tags = array_map(function($value) {
            $value = preg_replace('/\s+/isu', " ", trim(htmlspecialchars_decode($value)));
            $value = preg_replace("/[^\w\_\s]+/isu", '', $value);
            $value = mb_strtolower($value, 'UTF-8');
            return $value;
        }, $tags);

        $tags = array_unique($tags);
        $tags = array_chunk($tags, 9);
        $tags = $tags[0];


        $text = preg_replace("/!+/isu", "!", $advert->text);
        $text = preg_replace("/\<br\>/isu", PHP_EOL, $text);
        $text = html_entity_decode(htmlspecialchars_decode($advert->text));
        $text = strip_tags($text);
        $text = preg_replace("/[\x{fe00}-\x{fe0f}]/u", '', $text);

        $post['message'] = trim($text) . PHP_EOL;
        $post['message'] .= 'Контакты: ' . $advert->vk_owner_first_name . ' ' . $advert->vk_owner_last_name . ' ';
        $post['message'] .= PHP_EOL . 'Смотреть полностью: ' . $post['url'];
        foreach ($advert->contacts as $contact) {
            $post['message'] .= $contact->value . " ";
        }


        $post['url'] = Yii::app()->createAbsoluteUrl('items/item', array('city' => $advert->city->link, 'type' => $advert->type_data->link, 'link' => $advert->link, 'id' => $advert->id));


        $post['message'] .= PHP_EOL . 'Смотреть полностью: ' . $post['url'];

        $attachment = array(
            'media' => array(
                array(
                    'type' => 'text',
                    'text' => $post['message'],
                ),
                array(
                    'type' => 'link',
                    'url' => $post['url'],
                ),
            ),
        );


        $post = (object) $post;

        $ok = new OdnoklassnikiSDK();

        $images = array();
        foreach ($post->images as $key => $image) {
            $images['pic' . ($key)] = new CURLFile($image);
        }

        $photosCount = count($images);

        if ($photosCount > 0) {
            $upload_result = $ok->makeRequest('photosV2.getUploadUrl', array(
                //'aid' => $options['album_id'],
                'gid' => $options['group_id'],
                'count' => $photosCount,
            ));

            if (!empty($upload_result['upload_url'])) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $upload_result['upload_url']);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $images);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                $response = @json_decode($response, true);
                if (!empty($response['photos'])) {
                    $photos = array();
                    foreach ($response['photos'] as $id => $params) {
                        $photos[] = array('id' => $params['token']);
                    }

                    $attachment['media'][] = array(
                        'type' => 'photo',
                        'list' => $photos,
                    );
                }
            } else {
                die("UPLOAD ERROR!");
            }
        }

        $params = array(
            'type' => 'GROUP_THEME',
            'gid' => $options['group_id'],
            'attachment' => json_encode($attachment),
        );


        $post_id = $ok->makeRequest('mediatopic.post', $params);
        var_dump($post_id);
        if (!empty($post_id) && !is_array($post_id)) {
            $advert->ok_post_id = $post_id;
            $advert->save(false);

            foreach ($tags as $tag) {
                $ok->makeRequest('mediatopic.addTag', array(
                    'topic_id' => $post_id,
                    'tag' => $tag,
                ));
            }
        }
    }

}
