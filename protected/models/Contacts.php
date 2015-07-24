<?php
class Contacts extends CActiveRecord
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    public function tableName()
    {
        return "{{contacts}}";
    }
    
    public function rules()
    {
        return array(
            array('value', 'unique', 'allowEmpty' => false, 'criteria' => array(
                    'condition' => 't.type = :type and t.advert_id = :advert_id',
                    'params' => array('type' => $this->type, 'advert_id'=>  $this->advert_id)
                )),
        );
    }
    
    public function getUrl()
    {
        switch ($this->type) {
            case "vk":
                return '//vk.com/im?sel='.$this->value;
            case "phone":
                return 'tel:'.$this->value;
            default:
                return '#';
        }
    }
    
    public function getButton()
    {
        switch ($this->type) {
            case 'vk':
                $html = CHtml::openTag('a', array('href'=>  $this->getUrl(), 'target'=>'_blank', 'rel'=>'nofollow'));
                $html .= '<span class="glyphicon glyphicon-envelope"></span> ВКонтакте';
                $html .= CHtml::closeTag('a');
                return $html;
            case 'phone':
                $html = CHtml::openTag('a', array('href'=>  $this->getUrl(), 'rel'=>'nofollow'));
                $html .= '<nobr><span class="glyphicon glyphicon-earphone"></span>' . $this->value . '</nobr>';
                $html .= CHtml::closeTag('a');
                return $html;
        }
        return '<a href="'.$this->getUrl().'">'.$this->value.'</a>';
    }

}
