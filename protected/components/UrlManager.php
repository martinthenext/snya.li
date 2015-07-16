<?php
class UrlManager extends CUrlManager
{
    /**
	 * Преобразует любой текст в транслит для ссылки
	 * @param string $str
	 * @return string
	 * 
	 * @example echo Yii::app()->urlManager->translitUrl("Тест"); 
	 */
	public function translitUrl($str)
	{
        $replaces = array(
            'ч'=>'ch',
            'ё'=>'e',
            'ж'=>'zh',
            'ш'=>'sh',
            'щ'=>'shch',
            'ъ'=>'-',
            'ь'=>'-',
            'э'=>'e',
            'ю'=>'yu',
            'я'=>'ya',
        );
        $str = mb_strtolower($str, 'UTF-8');
        $str = str_ireplace(array_keys($replaces), $replaces, $str);
		$str = transliterator_transliterate('Any-Latin; Lower()', $str);
        $str = substr(preg_replace("~[^a-z0-9_\-]+~", "-", trim($str)), 0, 200);
		$str = urlencode($str);
        return str_replace("+", "%2B", $str);
	}

}