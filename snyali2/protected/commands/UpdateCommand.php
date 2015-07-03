<?php
class UpdateCommand extends CConsoleCommand
{
    /**
     * Обновляет список городов в базе данных
     * @param str $filename Путь к файлу городов, разделенных через запятую
     */
    public function actionCities($filename)
    {
        if (!file_exists($filename)) {
            throw new CException('Файл '.$filename.' не найден.');
        }

        $cities = file_get_contents($filename);
        $cities = explode(",", $cities);
        
        /**
         * Тырил с википедии с помощью js
         * https://ru.wikipedia.org/wiki/%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA_%D0%B3%D0%BE%D1%80%D0%BE%D0%B4%D0%BE%D0%B2_%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D0%B8_%D1%81_%D0%BD%D0%B0%D1%81%D0%B5%D0%BB%D0%B5%D0%BD%D0%B8%D0%B5%D0%BC_%D0%B1%D0%BE%D0%BB%D0%B5%D0%B5_100_%D1%82%D1%8B%D1%81%D1%8F%D1%87_%D0%B6%D0%B8%D1%82%D0%B5%D0%BB%D0%B5%D0%B9
         * var c = []; jQuery("table.needed").find("tbody").find("tr").find("td:eq(2)").each(function(k,v){ c.push(jQuery(v).find('a').attr('title')); }); console.log(c.join(','));
         * 
         * Необходимо очистить лишнюю информацию в скобках
         */
        $cities = array_map(function ($value) {
            // "Артем (город)" => "Артем"
            return trim(preg_replace("/\([\w\s]+\)/isu", '', $value));
        }, $cities);
        
        $api = new VkApi(4934698, '3djYV1o2nXEQCzydPGTn', '8b17eb5b67e4534cf64cc7ea70a8b488621d1bc38d48db89b77c9c9fa49499a9606d8aee6d34d72feb5d0');

        foreach ($cities as $city) {
            $result = $api->run("database.getCities", array(
                'country_id'=>1, // пока только для России
                'need_all'=>0,
                'count'=>1,
                'q'=>$city,
            ));
            sleep(1);
            
            /*
             * object(stdClass)#16 (4) {
                ["cid"]=>
                int(468)
                ["title"]=>
                string(22) "Прокопьевск"
                ["area"]=>
                string(37) "Прокопьевский район"
                ["region"]=>
                string(37) "Кемеровская область"
              }
             */
            
            if ($result !== false) {
                
                $result = (array) $result;
                        
                if (!isset($result[0])) {
                    echo "{$city} не найден".PHP_EOL;
                    continue;
                }
                
                $result = $result[0];
                
                $model = new Cities('update_vk');
                $model->attributes = array(
                    'vk_city_id'=>$result->cid,
                    'title'=>trim($result->title),
                    'area'=>!(empty($result->area)) ? trim($result->area) : '',
                    'region'=>!(empty($result->region)) ? trim($result->region) : '',
                );
                if ($model->validate()) {
                    $model->save();
                    echo $city." добавлен".PHP_EOL;
                } else {
                    echo $city." не добавлен".PHP_EOL;
                    var_dump($model->getErrors());
                }
            }
        }
        
        echo "Завершено".PHP_EOL;
    }
    
    public function actionMetro()
    {
        $str = "
                  <li>Автово</li>
                  <li>Адмиралтейская</li>
                  <li>Академическая</li>
                  <li>Балтийская</li>
                  <li>Василеостровская</li>
                  <li>Владимирская</li>
                  <li>Волковская</li>
                  <li>Бухарестская</li>
                  <li>Международная</li>
                  <li>Выборгская</li>
                  <li>Горьковская</li>
                  <li>Гостиный двор</li>
                  <li>Гражданский проспект</li>
                  <li>Девяткино</li>
                  <li>Достоевская</li>
                  <li>Елизаровская</li>
                  <li>Звёздная</li>
                  <li>Звенигородская</li>
                  <li>Кировский завод</li>
                  <li>Комендантский проспект</li>
                  <li>Крестовский остров</li>
                  <li>Купчино</li>
                  <li>Ладожская</li>
                  <li>Ленинский проспект</li>
                  <li>Лесная</li>
                  <li>Лиговский проспект</li>
                  <li>Ломоносовская</li>
                  <li>Маяковская</li>
                  <li>Московская</li>
                  <li>Московские ворота</li>
                  <li>Нарвская</li>
                  <li>Невский проспект</li>
                  <li>Новочеркасская</li>
                  <li>Обводный канал</li>
                  <li>Обухово</li>
                  <li>Озерки</li>
                  <li>Парк Победы</li>
                  <li>Парнас</li>
                  <li>Петроградская</li>
                  <li>Пионерская</li>
                  <li>Площадь Александра Невского</li>
                  <li>Площадь Александра Невского</li>
                  <li>Площадь Восстания</li>
                  <li>Площадь Ленина</li>
                  <li>Площадь Мужества</li>
                  <li>Политехническая</li>
                  <li>Приморская</li>
                  <li>Пролетарская</li>
                  <li>Проспект Большевиков</li>
                  <li>Проспект Ветеранов</li>
                  <li>Проспект Просвещения</li>
                  <li>Пушкинская</li>
                  <li>Рыбацкое</li>
                  <li>Садовая</li>
                  <li>Сенная площадь</li>
                  <li>Спасская</li>
                  <li>Спортивная</li>
                  <li>Старая деревня</li>
                  <li>Технологический институт</li>
                  <li>Технологический институт</li>
                  <li>Удельная</li>
                  <li>Улица Дыбенко</li>
                  <li>Фрунзенская</li>
                  <li>Чёрная речка</li>
                  <li>Чернышевская</li>
                  <li>Чкаловская</li>
                  <li>Электросила</li>
";
        $pattern = "/\<li>(\w+)\<\/li\>/isu";
        preg_match_all($pattern, $str, $matches, PREG_SET_ORDER);
        foreach ($matches as $metro) {
            $model = new Metro();
            $model->link = Yii::app()->urlManager->translitUrl($metro[1]);
            $model->title = $metro[1];
            $pattern = preg_quote($metro[1], "/");
            $pattern = preg_replace("/ая$/iu", "(ая|ой)", $pattern);
            $pattern = preg_replace("/и$/iu", "(и|ах)", $pattern);
            $pattern = preg_replace("/т$/iu", "(т|те)", $pattern);
            $model->pattern = "/{$pattern}/isu";
            $model->city_id = 2;
            $model->save();
        }
    }
    
    public function actionIndex()
    {
        $api = new VkApi(4934698, '3djYV1o2nXEQCzydPGTn', '8b17eb5b67e4534cf64cc7ea70a8b488621d1bc38d48db89b77c9c9fa49499a9606d8aee6d34d72feb5d0');

        $cities = Cities::model()->findAll();
        $citiesAllowed = [];
        foreach ($cities as $city) {
            $citiesAllowed[$city->vk_city_id] = $city->id;
        }

        // Выбираем активные запросы для поиска объявлений
        $criteria = new CDbCriteria();
        $criteria->condition = 't.enabled = 1';
        $criteria->order = 't.id desc';

        $queries = SearchQueries::model()->findAll($criteria);
        foreach ($queries as $query) {
            echo "Поиск \"{$query->keyword}\"" . PHP_EOL;
            $params = [
                'q' => $query->keyword . " - - -риелтор -комиссия",
                'extended' => 1, // получаем информацию о пользователе
                'count' => 1000,
            ];
            sleep(1);
            $result = $api->run('newsfeed.search', $params);

            foreach ($result as $post) {

                //[0]=>int(1000)
                if (!is_object($post)) {
                    continue;
                }

                // посты из групп не интересуют
                if ($post->owner_id < 1) {
                    continue;
                }

                // интересуют только записи типа post
                if ($post->post_type !== 'post') {
                    continue;
                }

                if (empty($post->user->screen_name)) {
                    continue;
                }



                sleep(1);
                $userResult = $api->run('users.get', [
                    'user_ids' => $post->user->screen_name,
                    'fields' => 'status,activities,interests,about,city,country,contacts,screen_name,photo_100',
                        ], false);
                $userResult = (array) $userResult;
                $post->user = !empty($userResult[0]) ? $userResult[0] : false;

                // Определеляем город в посте
                $city = false;
                if (!empty($post->geo->city_id)) {
                    $city = $post->geo->city_id;
                }

                if (empty($post->user) || (empty($post->user->city) && !$city)) {
                    continue;
                }

                if (!$city) {
                    $city = $post->user->city;
                }

                if (!isset($citiesAllowed[$city])) {
                    continue;
                }

                $transaction = Yii::app()->db->beginTransaction();

                $adwert = new Adverts('import');
                $adwert->text = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', nl2br($post->text));
                $adwert->city_id = $citiesAllowed[$city];
                $adwert->vk_owner_avatar = !empty($post->user->photo_100) ? $post->user->photo_100 : '';
                $adwert->vk_owner_first_name = $post->user->first_name;
                $adwert->vk_owner_last_name = $post->user->last_name;
                $adwert->type = $query->type;
                $adwert->created = $post->date;
                $adwert->vk_post_id = $post->id;
                $adwert->vk_owner_id = $post->owner_id;

                $adwert->postData = $post;

                if ($adwert->validate()) {
                    $adwert->save();

                    if (!empty($post->attachments)) {
                        foreach ($post->attachments as $attachment) {

                            // Содержит ссылку или видео
                            if ($attachment->type == 'link' || $attachment->type == 'video') {
                                echo "Пост содержит ссылки или видео, пропущен." . PHP_EOL;
                                $transaction->rollback();
                                $transaction->active = false;
                                break;
                            }

                            if ($attachment->type !== 'photo' || empty($attachment->photo->width)) {
                                continue;
                            }

                            $attachmentModel = new Attachments('import');
                            $attachmentModel->advert_id = $adwert->id;
                            $attachmentModel->owner_id = $attachment->photo->owner_id;
                            $attachmentModel->pid = $attachment->photo->pid;
                            $attachmentModel->aid = $attachment->photo->aid;
                            $attachmentModel->type = $attachment->type;
                            $attachmentModel->src = !empty($attachment->photo->src) ? $attachment->photo->src : '';
                            $attachmentModel->src_big = !empty($attachment->photo->src_big) ? $attachment->photo->src_big : '';
                            $attachmentModel->src_xbig = !empty($attachment->photo->src_xbig) ? $attachment->photo->src_xbig : '';
                            $attachmentModel->src_xxbig = !empty($attachment->photo->src_xxbig) ? $attachment->photo->src_xxbig : '';
                            $attachmentModel->src_xxxbig = !empty($attachment->photo->src_xxxbig) ? $attachment->photo->src_xxxbig : '';

                            $attachmentModel->width = $attachment->photo->width;
                            $attachmentModel->height = $attachment->photo->height;

                            if ($attachmentModel->validate()) {
                                $attachmentModel->save();
                            } else {
                                $transaction->rollback();
                                $transaction->active = false;
                                var_dump($attachmentModel->getErrors());
                                break;
                            }
                        }
                    }
                } else {
                    $transaction->rollback();
                    var_dump($adwert->getErrors());
                    continue;
                }

                if ($transaction->active) {
                    $transaction->commit();
                    echo 'Добавлен пост: https://vk.com/wall' . $post->owner_id . '_' . $post->id . ' #' . $adwert->relevance . PHP_EOL;
                }
            }
        }
    }
}