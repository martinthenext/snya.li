<?php
class Attachments extends CActiveRecord
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    public function tableName()
    {
        return "{{attachments}}";
    }
    
    public function getSrc_lightbox()
    {
        if (!empty($this->src_xxxbig)) {
            return $this->src_xxxbig;
        }
        
        if (!empty($this->src_xxbig)) {
            return $this->src_xxbig;
        }
        
        if (!empty($this->src_xbig)) {
            return $this->src_xbig;
        }
        
        if (!empty($this->src_big)) {
            return $this->src_big;
        }
        
        return $this->src;
    }

}
