<?php

class Adverts extends CActiveRecord
{

    /**
     * Контакты для поста
     * @var array
     */
    protected $_newContacts = array();

    // Максимальное расстояние между ключевыми словами в посте
    const KEYWORDS_DISTANCE = "1,80";
    // Рейтинг за фото
    const RELEVANCE_ATTACHMENT = 50;
    // Рейтинг за паттерн
    const RELEVANCE_PATTERN = 180;
    // Рейтинг за количество слов
    const RELEVANCE_WORD = 3;
    // Рейтинг за контакты
    const RELEVANCE_CONTACT = 300;
    // Если указали метро
    const RELEVANCE_METRO = 150;
    // Максимальное количество отображаемых похожих записей
    const LIMIT_SIMILARS = 10;
    // Максимальная длина короткого описания
    const LIMIT_SHORT_CONTENT = 255;

    public $filters = array(
        'import' => array(
            'ContentBlacklist',
            'KeywordsDistance',
            'UserStopWords',
            'UserBlacklist',
            'Metro',
            'Relevance',
            'Contacts',
        ),
    );

    /**
     * Полный набор данных поста
     * @var type
     */
    public $postData;

    /**
     * Полный набор данных пользователя
     * @var type 
     */
    public $userData;

    /**
     * @todo Определить необходимый список ключевых слов и искать их в тексте
     * @var array ключевые слова для meta
     */
    public $defaultKeywords = array(
        'дом', 'квартиру', 'жилье'
    );
    
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{adverts}}";
    }

    public function rules()
    {
        return array(
            array('vk_post_id', 'unique', 'allowEmpty' => false, 'criteria' => array(
                    'condition' => 't.vk_owner_id = :vk_owner_id',
                    'params' => array('vk_owner_id' => $this->vk_owner_id)
                )),
            array('vk_owner_first_name, vk_owner_last_name', 'length', 'min' => 1),
            array('text', 'length', 'min' => 10),
            array('metro_id, vk_post_id, vk_owner_id, text, city_id, type, created, updated, enabled', 'safe'),
            array('enabled', 'default', 'value' => 1),
            array('metro_id', 'default', 'value' => 0),
            array('vk_owner_id, vk_post_id, city_id', 'required'),
            array('relevance', 'default', 'value' => -1000),
        );
    }

    public function afterSave()
    {
        if ($this->isNewRecord && $this->scenario == 'import') {

            foreach ($this->_newContacts as $contact) {
                $model = new Contacts();
                $model->type = $contact['type'];
                $model->value = $contact['value'];
                $model->advert_id = $this->id;
                if ($model->validate()) {
                    $model->save();
                }
            }

            $this->_newContacts = array();
        }

        return parent::afterSave();
    }

    public function beforeValidate()
    {
        $this->updated = time();

        if (!empty($this->filters[$this->scenario]) && is_array($this->filters[$this->scenario])) {
            foreach ($this->filters[$this->scenario] as $filter) {
                $filter = 'filter' . $filter;
                if (!$this->$filter()) {
                    return false;
                }
            }
        }

        return parent::beforeValidate();
    }

    public function relations()
    {
        return array(
            'city' => array(self::BELONGS_TO, 'Cities', 'city_id'),
            'type_data' => array(self::BELONGS_TO, 'AdvertTypes', 'type'),
            'attachments' => array(self::HAS_MANY, 'Attachments', array('advert_id' => 'id')),
            'contacts' => array(self::HAS_MANY, 'Contacts', array('advert_id' => 'id')),
            'metro' => array(self::BELONGS_TO, 'Metro', array('metro_id' => 'id')),
        );
    }

    ######## ФИЛЬТРЫ ############

    protected function filterContentBlacklist()
    {
        $patterns = array(
            '/исполнения любого желания/isu',
            '/куплю тебе дом/isu',
        );

        $text = strip_tags($this->text);

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return false;
            }
        }

        return true;
    }

    protected function filterUserStopWords()
    {

        // Поиск стоп-слов у пользователя
        if (!empty($this->postData->user) && is_object($this->postData->user)) {

            $user = $this->postData->user;

            $context = array();
            $context[] = !empty($user->first_name) ? $user->first_name : '';
            $context[] = !empty($user->last_name) ? $user->last_name : '';
            $context[] = !empty($user->interests) ? $user->interests : '';
            $context[] = !empty($user->activities) ? $user->activities : '';
            $context[] = !empty($user->about) ? $user->about : '';
            $context[] = !empty($user->status) ? $user->status : '';
            $context = implode(" ", $context);
        }


        $patterns = array(
            "/агент/iu",
            "/объявлени(я|й)/iu",
            "/agent/iu",
            "/аренд/iu",
            "/arend/iu",
            "/rent/iu",
            "/кварт/iu",
            "/kvart/iu",
            "/комн/iu",
            "/komn/iu",
            "/sdam/",
            "/сдам/iu",
            "/сним/iu",
            "/сним/iu",
            "/недвиж/iu",
            "/nedvi/iu",
            "/жилье/iu",
            "/jilye/iu",
            "/r(i|e)(e|a)l/iu",
            "/р(е|и)(е|э)л/iu",
            "/ндвижимость/iu",
        );

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $context)) {
                $this->addError('vk_owner_id', "Сработал стоп-паттерн {$pattern} для профиля пользователя.");
                return false;
            }
        }

        return true;
    }

    /**
     * Проверяет наличие пользователя в черном списке
     * @return boolean
     */
    protected function filterUserBlacklist()
    {
        $criteria = new CDbCriteria();
        $criteria->condition = 't.vk_user_id = :vk_owner_id';
        $criteria->params = array('vk_owner_id' => $this->vk_owner_id);

        $count = UsersBlacklist::model()->count($criteria);
        if ($count > 0) {
            $this->addError('vk_owner_id', "Пользователь {$this->vk_owner_id} найден в стоп-листе.");
            return false;
        }
        return true;
    }

    /**
     * Проверяет расстояние в тексте между ключевыми
     * @todo Допилить или убрать
     * @return boolean
     */
    protected function filterKeywordsDistance()
    {

        $keywords = SearchQueries::model()->findAllByAttributes(array(
            'type' => $this->type
        ));

        $patterns = array();
        foreach ($keywords as $keyword) {
            /**
             * @todo Возможно, фильтр слишком жесткий
             */
            if (preg_match('/.*' . preg_replace('/\s+/iu', ".{" . self::KEYWORDS_DISTANCE . "}", '[^\w]+' . preg_quote($keyword->keyword, '/')) . '[^\w]+.*/isu', $this->text)) {
                return true;
            }
        }

        $this->addError('text', "Слишком большая дистанция для ключевых слов https://vk.com/wall{$this->vk_owner_id}_{$this->vk_post_id}");
        return false;
    }

    protected function filterRelevance()
    {
        $patterns = array(
            // регуляка, множитель
            array("/агент(ство|а|у)/iu", -1),
            array("/ри(е|э)лтор/iu", -2),
            array("/ко(м)?миси(я|ю)/iu", -2),
            array("/(\%|процент)/iu", -1),
            array("/метро/iu", 1),
        );

        $this->relevance = 0;

        // Рейтинг за фото
        if (!empty($this->postData->attachments)) {
            $this->relevance += count($this->postData->attachments) * self::RELEVANCE_ATTACHMENT;
        }

        // Рейтинг за стоп-слова
        foreach ($patterns as $pattern) {
            if (preg_match($pattern[0], $this->text)) {
                $this->relevance += $pattern[1] * self::RELEVANCE_PATTERN;
            }
        }

        // Считаем количество слов в тексте
        $wordsCount = preg_match_all("/\w+(?>\\W+|$)/iu", strip_tags($this->text));
        $wordsCount = ($wordsCount > 200) ? 50 : $wordsCount;
        $this->relevance += $wordsCount * self::RELEVANCE_WORD;

        if ($this->metro_id > 0) {
            $this->relevance += self::RELEVANCE_METRO;
        }

        return true;
    }

    protected function filterContacts()
    {


        $phones = array();
        $emails = array();

        $patterns = array(
            "/(?P<phone>((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10})/isu",
            "/(?P<phone>8\d{10})/isu",
            "/(?P<phone>8\-\d{3}\-\d{3}\-\d{2}\-\d{2})/isu",
            "/(?P<phone>8 \d{3} \d{3} \d{2} \d{2})/isu",
            '/(?P<email>(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\])))/iu',
        );

        $text = str_replace("<br>", PHP_EOL, $this->text);

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches, PREG_PATTERN_ORDER)) {
                if (!empty($matches['phone'])) {
                    foreach ($matches['phone'] as $phone) {
                        $phone = preg_replace("/^8/iu", "+7", $phone);
                        $phone = '+' . preg_replace("/\D/isu", "", $phone);
                        if (preg_match("/^\+7\d+$/iu", $phone)) {
                            $phones[] = $phone;
                        }
                    }
                }

                if (!empty($matches['email'])) {
                    foreach ($matches['email'] as $email) {
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $emails[] = $email;
                        }
                    }
                }
            }
        }

        $phones = array_unique($phones);
        $emails = array_unique($emails);

        if (!empty($phones)) {
            $this->_newContacts = array_map(function($value) {
                return array(
                    'type' => 'phone',
                    'value' => $value,
                );
            }, $phones);
        }

        if (!empty($emails)) {
            $this->_newContacts = array_map(function($value) {
                return array(
                    'type' => 'email',
                    'value' => $value,
                );
            }, $emails);
        }

        $this->_newContacts[] = array(
            'type' => 'vk',
            'value' => $this->vk_owner_id,
        );

        $this->relevance += ceil((int) (!empty($this->_newContacts)) * self::RELEVANCE_CONTACT * 0.8);

        if (!empty($phones)) {
            $this->relevance = count($phones) * self::RELEVANCE_CONTACT + $this->relevance;
        }

        return true;
    }

    public function getTags()
    {
        $tags = array();

        if ($this->created > time() - 86400) {
            $tags[] = array(
                'title' => 'Новое',
                'class' => 'success',
                'url' => '#',
            );
        }

        if ($this->created < time() - 14 * 86400) {
            $tags[] = array(
                'title' => 'Старое',
                'class' => 'warning',
                'url' => '#',
            );
        }

        $tags[] = array(
            'title' => $this->type_data->title,
            'class' => 'info',
            'url' => '#',
        );

        $tags[] = array(
            'title' => 'г. ' . $this->city->title,
            'class' => 'primary',
            'url' => '#',
        );

        if (!empty($this->metro->title)) {
            $tags[] = array(
                'title' => 'м. ' . $this->metro->title,
                'class' => 'primary',
                'url' => '#',
            );
        }
        return $tags;
    }

    public function getLink()
    {
        $link = strip_tags($this->text);
        $link = preg_replace("/\W\s+/isu", "", $link);
        $link = mb_substr($link, 0, 90);
        return Yii::app()->urlManager->translitUrl($link);
    }

    public function filterMetro()
    {
        $metros = Metro::model()->findAllByAttributes(array('city_id' => $this->city_id));
        if (!empty($metros)) {
            foreach ($metros as $metro) {
                $text = strip_tags($this->text);
                if (preg_match($metro->pattern, $text)) {
                    $this->metro_id = $metro->id;
                    return true;
                }
            }
        }
        return true;
    }

    /**
     * Подготавливает контент для отображения на странице
     * @return string контент
     */
    public function getContent()
    {
        $text = preg_replace("/!+/isu", "!", $this->text);
        /**
         * Удаляем селекторы начертания
         */
        $text = preg_replace("/[\x{fe00}-\x{fe0f}]/u", '', $text);
        return preg_replace("/#([\w_]+)/isu", "<a href=\"" . Yii::app()->request->hostInfo . "/search?search=%23$1\">#$1</a> ", $text);
    }

    /**
     * @return string короткое описание
     */
    public function getShortContent()
    {

        $text = $this->text;

        /**
         * Удаляем селекторы начертания
         */
        $text = preg_replace("/[\x{fe00}-\x{fe0f}]/u", '', $text);

        /**
         * Заменяем UTF-8 красный восклицательный знак
         * @todo Сделать таблицу замены
         */
        $text = preg_replace("/\x{2757}/u", '!', $text);

        // Удаляем все html тэги
        //$text = strip_tags($text);
        // Переводы строк заменяем на пробелы
        // Множество пробелов превращаем в один
        $text = preg_replace("/\s+/isu", " ", $text);

        $text = preg_replace("/!+/isu", "!", $text);
        $text = preg_replace("/(\s+(\,|\.|\?|\!|\:|\;))/isu", "$2", $text);
        $text = preg_replace("/(\,|\.|\?|\!|\:|\;)(\w+)/isu", "$1 $2", $text);
        $text = preg_replace("/^[^\w#]+/isu", "", $text);

        $text = mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($text, 1, mb_strlen($text, 'UTF-8'), 'UTF-8');



        if (mb_strlen($text, 'UTF-8') <= self::LIMIT_SHORT_CONTENT) {

            $text = preg_replace("/#([\w_]+)/isu", "<a href=\"" . Yii::app()->request->hostInfo . "/search?search=%23$1\">#$1</a> ", $text);
            $text = nl2br($text);

            return $text;
        }

        $words = preg_split("/(\s|\?|\!)/isu", $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        $text = '';

        foreach ($words as $word) {
            if (mb_strlen($word, 'UTF-8') + mb_strlen($text, 'UTF-8') <= 255) {
                $text .= $word;
            } else {
                break;
            }
        }

        $text = preg_replace("/(\w+)[^\w]+$/isu", "$1", $text);
        $text = preg_replace("/#([\w_]+)/isu", "<a href=\"" . Yii::app()->request->hostInfo . "/search?search=%23$1\">#$1</a> ", $text) . '...';
        $text = nl2br($text);


        return $text;
    }
    
    /**
     * 
     * @return string meta keywords content
     */
    public function getKeywords()
    {
        $keywords = array();
        foreach ($this->getTags() as $tag) {
            $keywords[] = $tag['title'];
        }
        $keywords = array_merge($keywords, $this->defaultKeywords);
        
        return mb_substr(CHtml::encode(implode(", ", $keywords)), 0, 255);
    }


    /**
     * 
     * @return string meta description content
     */
    public function getShortDescription()
    {
        return mb_substr(CHtml::encode(strip_tags(preg_replace("/\.{3}$/isu", '', $this->getShortContent()))), 0, 255);
    }
    
    /**
     * Возвращает похожие объявления
     * @todo Доработать алгоритм выбора похожих
     */
    public function getSimilars()
    {
        $criteria = new CDbCriteria();
        $criteria->condition = 't.id != :id and t.city_id = :city_id and t.type = :type and t.created <= :created and t.created >= :created - 30 * 24 * 60 * 60';
        $criteria->params = array(
            'city_id' => $this->city_id,
            'type' => $this->type,
            'created' => $this->created,
            'id' => $this->id,
        );
        $criteria->order = 't.created desc';
        $criteria->limit = self::LIMIT_SIMILARS;

        return self::model()->cache(24 * 60 * 60)->findAll($criteria);
    }

}
