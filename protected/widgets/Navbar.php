<?php

Yii::import('zii.widgets.CMenu');

/**
 * @todo Добавить возможность указать navbar-right, navbar-collspase, navbar-left
 */
class Navbar extends CMenu
{

    public $navbarClass = 'navbar navbar-default navbar-fixed-top';
    public $activeClass = 'active';
    public $withoutSearch = false;
    public $label;

    public function run()
    {
        return parent::run();
    }

    public function renderMenu($items)
    {
        if (empty($this->label)) {
            $this->label = Yii::app()->name;
        }

        if (count($items)) {
            echo CHtml::openTag('nav', array('class' => $this->navbarClass));
            echo CHtml::openTag('div', array('class' => 'container'));
            echo CHtml::openTag('div', array('class' => 'navbar-header'));
            echo CHtml::openTag('button', array(
                'class' => 'navbar-toggle collapsed',
                'data-toggle' => "collapse",
                'data-target' => "#navbar",
                'aria-expanded' => "false",
                'aria-controls' => "navbar"
            ));
            echo CHtml::tag('span', array('class' => 'sr-only'), Yii::t("navbar", "Переключить меню"));
            echo CHtml::tag('span', array('class' => 'icon-bar'), '');
            echo CHtml::tag('span', array('class' => 'icon-bar'), '');
            echo CHtml::tag('span', array('class' => 'icon-bar'), '');
            echo CHtml::closeTag('button');
            echo CHtml::openTag('a', array('href' => Yii::app()->createAbsoluteUrl("/"), 'class' => 'navbar-brand'));
            echo $this->label;
            echo CHtml::closeTag('a');
            echo CHtml::closeTag('div');

            echo CHtml::openTag('div', array('class' => 'navbar-collapse collapse', 'id' => 'navbar'));
            echo CHtml::openTag('ul', array('class' => 'nav navbar-nav'));
            $this->renderMenuRecursive($items);
            echo CHtml::closeTag('ul');

            #begin search form


            if (!$this->withoutSearch) {
                echo CHtml::form(Yii::app()->createAbsoluteUrl("items/search"), 'get', array('class' => 'navbar-form navbar-left', 'role' => 'search', 'id' => 'search-from'));

                if (!empty(City::getModel()->link)) {
                    echo CHtml::hiddenField('city', City::getModel()->link);
                }

                if (!empty(Yii::app()->request->getParam('type')) && preg_match("/^\w+$/isu", Yii::app()->request->getParam('type'))) {
                    echo CHtml::hiddenField('type', Yii::app()->request->getParam('type'));
                }

                echo CHtml::tag('div', array('class' => 'form-group'));
                echo CHtml::textField('search', '', array('placeholder' => 'Поиск', 'class' => 'form-control'));
                echo CHtml::closeTag('div');
                echo CHtml::submitButton('Найти', array('class' => 'btn btn-default'));
                echo CHtml::endForm();
                echo CHtml::closeTag('div');
                # end search form
            }
            echo CHtml::closeTag('div');
            echo CHtml::closeTag('nav');
        }
    }

    protected function renderMenuRecursive($items)
    {
        $count = 0;
        $n = count($items);
        foreach ($items as $item) {
            $count++;
            $options = isset($item['itemOptions']) ? $item['itemOptions'] : array();
            $class = array();
            if ($item['active'] && $this->activeCssClass != '')
                $class[] = $this->activeCssClass;
            if ($count === 1 && $this->firstItemCssClass !== null)
                $class[] = $this->firstItemCssClass;
            if ($count === $n && $this->lastItemCssClass !== null)
                $class[] = $this->lastItemCssClass;
            if ($this->itemCssClass !== null)
                $class[] = $this->itemCssClass;
            if ($class !== array()) {
                if (empty($options['class']))
                    $options['class'] = implode(' ', $class);
                else
                    $options['class'].=' ' . implode(' ', $class);
            }

            if (isset($item['items']) && count($item['items'])) {
                $options['class'] = !empty($options['class']) ? $options['class'] .= ' dropdown' : 'dropdown';

                $item['linkOptions']['class'] = !empty($item['linkOptions']['class']) ? $item['linkOptions']['class'] .= ' dropdown-toggle' : 'dropdown-toggle';
                $item['linkOptions']['data-toggle'] = "dropdown";
                $item['linkOptions']['role'] = "button";
                $item['linkOptions']['aria-expanded'] = "false";

                $item['label'] .= ' <span class="caret"></span>';

                $item['submenuOptions']['class'] = 'dropdown-menu';
                $item['submenuOptions']['role'] = 'menu';
            }

            echo CHtml::openTag('li', $options);

            $menu = $this->renderMenuItem($item);
            if (isset($this->itemTemplate) || isset($item['template'])) {
                $template = isset($item['template']) ? $item['template'] : $this->itemTemplate;
                echo strtr($template, array('{menu}' => $menu));
            } else
                echo $menu;

            if (isset($item['items']) && count($item['items'])) {
                echo "\n" . CHtml::openTag('ul', isset($item['submenuOptions']) ? $item['submenuOptions'] : $this->submenuHtmlOptions) . "\n";
                $this->renderMenuRecursive($item['items']);
                echo CHtml::closeTag('ul') . "\n";
            }

            echo CHtml::closeTag('li') . "\n";
        }
    }

}
