<?php

class PostCommand extends CConsoleCommand
{

    public function actionIndex()
    {
        $_SERVER['HTTP_HOST'] = 'snya.li';
        $_SERVER['SERVER_NAME'] = 'snya.li';
        Yii::app()->urlManager->baseUrl = "http://snya.li";

        $options = [
            'app_id' => 4943537,
            'app_secret' => '9ZY39OBWAzQVrB88SFzu',
            'app_authkey' => 'b5c5cca893e16d19fa16c94f01e369d1d7d7d190ff0be6cc8531e788211315f075055e5bdc25428ef7663',
            'group_id' => 94983589,
            'photo_album_id' => 216531844,
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

        $httpClient = new GuzzleHttp\Client(['defaults' => [
                'verify' => false
        ]]);




        foreach ($advert->attachments as $attachment) {

            if ($attachment->type != 'photo' || empty($attachment->src)) {
                continue;
            }


            $filename = tempnam('/tmp', 'snyali_image');

            try {
                $result = $httpClient->get($attachment->src_lightbox, ['save_to' => $filename]);
                if ($result->getStatusCode() == 200 && filesize($filename)) {
                    $size = @getimagesize($filename);
                    if (!is_array($size) || $size[0] < 200 || $size[1] < 200 || !preg_match("/^image\/(jpeg|jpg)$/isu", $size['mime'])) {
                        @unlink($filename);
                        continue;
                    }
                    rename($filename, $filename . ".jpeg");
                    $filename .= ".jpeg";
                    $post['images'][] = $filename;
                } else {
                    @unlink($filename);
                }
            } catch (\GuzzleHttp\Exception $e) {
                @unlink($filename);
            }
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
        $post['message'] .= trim(str_replace('<br>', PHP_EOL, html_entity_decode(htmlspecialchars_decode($advert->text)))) . PHP_EOL;
        $post['message'] .= 'Контакты: ' . $advert->vk_owner_first_name . ' ' . $advert->vk_owner_last_name . ' ';
        foreach ($advert->contacts as $contact) {
            $post['message'] .= $contact->value . " ";
        }
        $post['url'] = Yii::app()->createAbsoluteUrl('items/item', array('city' => $advert->city->link, 'type' => $advert->type_data->link, 'link' => $advert->link, 'id' => $advert->id));

        $post['message'] .= PHP_EOL . 'Смотреть полностью: ' . $post['url'];

        if (!empty($post['message']) || !empty($post['images'])) {
            // Данные для API
            $api = new VkApi($options['app_id'], $options['app_secret'], $options['app_authkey']);

            if (!empty($post['images'])) {
                $result = $api->run('photos.getUploadServer', array(
                    'group_id' => (int) $options['group_id'],
                    'album_id' => (int) $options['photo_album_id'],
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

}
