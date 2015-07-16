<?php

class City
{
    // Текущая модель города
    protected static $currentModel = false;
    
    // Если не удалось определить город, ставим Москву по умолчанию
    const DEFAULT_CITY_ID = 1;
    
    /**
     * Должна выполняться в beforeAction всех контроллеров, где используется город
     * @todo Убрать автоназначение города по-умолчанию и показывать диалоговое окно выбора 
     */
    public static function run()
    {
        self::$currentModel = self::getUserCityModel();
    }
    
    /**
     * Перенаправляет пользователя на страницу с городом
     * @param string $url
     * @param array $params
     */
    public static function redirect($redirectUrl = 'items/index', $params = array())
    {
        if (!is_array($params)) {
            throw new CException('$params может быть только массивом');
        }
        
        if (self::$currentModel->id) {
            $url = Yii::app()->createAbsoluteUrl($redirectUrl, array_merge(array(
                'city'=>self::$currentModel->link
            ), $params));
            Yii::app()->request->redirect($url);
        }
    }
    
    /**
     * Устанавливает текущий город
     * @param int $cityId
     * @return bool Если успешно, то true
     */
    public static function setCurrentCity($cityId)
    {
        if ($cityModel = Cities::model()->findByPk((int) $cityId)) {
            self::$currentModel = $cityModel;
            Yii::app()->user->setState('city_id', $cityModel->id);
            return true;
        } 
        
        return false;
    }
    
    public static function getModel()
    {
        return self::$currentModel;
    }

    /**
     * Определяет город по ip и возвращает модель
     * @return mixed Модель Cities
     */
    public static function getGeoCityModel()
    {
        $ip = Yii::app()->request->getUserHostAddress();
        $rf = new ReflectionClass(\IgI\SypexGeo\SxGeo::class);
        $classFile = $rf->getFileName();
        $db_file = 'SxGeoCity.dat';
        if (!file_exists($db_file)) {
            $db_file = dirname($classFile) . DIRECTORY_SEPARATOR . $db_file;
        }
        $geo = new \IgI\SypexGeo\SxGeo($db_file);
        $city = $geo->getCityFull($ip);


        // Если удалось определить город
        if ($city !== false) {
            // Если geo_city_id уже сопоставлен с городом ВК
            if ($cityModel = Cities::model()->findByAttributes(array('geo_city_id' => $city['city']['id']))) {
                return $cityModel;
            }

            // Находим город по названию в базе и записываем geo_city_id
            $criteria = new CDbCriteria();
            $criteria->condition = 't.title like :title and t.with_geo = 0';
            $criteria->params = array(
                'title' => $city['city']['name_ru'],
            );
            $criteria->limit = 1;
            
            if ($cityModel = Cities::model()->find($criteria)) {
                // Помечаем успешное сопоставление города в базах
                $cityModel->with_geo = 1; 
                $cityModel->geo_city_id = (int) $city['city']['id'];
                // Координаты города
                $cityModel->geo_lat = doubleval($city['city']['lat']);
                $cityModel->geo_lon = doubleval($city['city']['lon']);
                $cityModel->geo_region_id = (int) $city['region']['id'];
                // На будущее делаем ссылку для региона
                $cityModel->geo_region_link = Yii::app()->urlManager->translitUrl($city['region']['name_ru']);
                $cityModel->geo_region_title = $city['region']['name_ru'];
                if ($cityModel->validate() && $cityModel->save()) {
                    return $cityModel;
                }
            }
        }

        return false;
    }
    
    /**
     * Возвращает модель города текущей сессии. 
     * Если в сессии нет города, назначает ее
     * @param bool $allowDefault Если true, подставляется город по умолчанию
     * @return Cities
     */
    public static function getUserCityModel($allowDefault = true)
    {
        // 
        if (Yii::app()->user->hasState('city_id')) {
            if ($cityModel = Cities::model()->findByPk(Yii::app()->user->getState('city_id'))) {
                return $cityModel;
            }
        } else if ($cityModel = self::getGeoCityModel()) {
            Yii::app()->user->setState('city_id', $cityModel->id);
            return $cityModel;
        }
        
        // Если города нет, отдаем город по умолчанию
        return ($allowDefault) ? Cities::model()->findByPk(self::DEFAULT_CITY_ID) : false;
    }

}
