<?php
class AdvertActions extends CActiveRecord
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    public function tableName()
    {
        return "{{advert_actions}}";
    }

}
