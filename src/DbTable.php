<?php


namespace iAvatar777\services\DbTable;

use cs\services\Str;
use cs\services\VarDumper;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Html as yiiHtml;

class DbTable extends Widget
{
    /**
     * @var array параметры (аттрибуты) таблицы
     * по умолчанию ['class' => 'table table-hover table-striped', 'style' => 'width:auto;']
     */
    public $options;

    public $name;

    public $model;

    public $description;

    /**
     * @var array
     * [
     *      'name'
     *      'description'
     *      'isRequired' bool по умолчанию false, так
     *      'type' string по умолчанию 'string'
     * ]
     */
    public $params;

    /** @var array дублирует params */
    public $columns;

    public function init()
    {
        if (empty($this->options)) {
            $this->options = [];
        }
        if (empty($this->columns)) {
            $this->columns = $this->params;
        }
    }

    /**
     * Печатает все объединения категории
     *
     * @return string HTML
     */
    public function run()
    {
        $html = [];
        $headers = ['#', 'Название', 'Обязательный?', 'Тип данных', 'Описание'];
        $r = [];
        foreach ($headers as $header) {
            $r[] = Html::tag('th', $header);
        }
        $rows = [Html::tag('tr', join('', $r))];

        $index = 1;
        foreach ($this->columns as $param) {
            $rows[] = $this->tr($param, $index);
            $index++;
        }
        $options = ['class' => 'table table-hover table-striped', 'style' => 'width:auto;'];
        $options = ArrayHelper::merge($options, $this->options);

        if ($this->name) {
            $html[] = Html::tag('p', 'Таблица: '. Html::tag('code', $this->name));
        }
        if ($this->model) {
            $html[] = Html::tag('p', 'Модель: '. Html::tag('code', $this->model));
        }
        if ($this->description) {
            $html[] = Html::tag('p', 'Описание: '. $this->description);
        }
        $html[] = Html::tag('table', join('', $rows), $options);

        return join('', $html);
    }

    private function tr($param, $index)
    {
        $td = [];
        $td[] = Html::tag('td', $index);
        $td[] = Html::tag('td', Html::tag('code', $param['name']));
        $isRequired = ArrayHelper::getValue($param, 'isRequired', false);
        if ($isRequired) {
            $html = Html::tag('span', 'Да', ['class' => 'label label-success']);
        } else {
            $html = Html::tag('span', 'Нет', ['class' => 'label label-default']);
        }
        $td[] = Html::tag('td', $html);
        $type = ArrayHelper::getValue($param, 'type', 'string');
        $td[] = Html::tag('td', $type);
        $td[] = Html::tag('td', ArrayHelper::getValue($param, 'description', ''));

        return Html::tag('tr', join('', $td));
    }
}