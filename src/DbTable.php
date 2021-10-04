<?php

namespace common\services\documentation;

use common\models\information_schema\InnoDbColumn;
use common\models\information_schema\InnoDbTable;
use cs\services\Str;
use cs\services\VarDumper;
use yii\base\Widget;
use yii\db\Connection;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Html as yiiHtml;

/**
 * Выводит на экран список полей таблицы
 * Если в списке параметров нет поля а в БД оно есть, то оно выводится. Для этого оно сканируется в таблице dbInfo.
 *
 */
class DbTable extends Widget
{
    /**
     * @var array параметры (аттрибуты) таблицы
     * по умолчанию ['class' => 'table table-hover table-striped', 'style' => 'width:auto;']
     */
    public $options;

    /** @var string название компонента базы данных где расположена таблица */
    public $db = 'db';

    public $name;

    public $model;

    public $description;

    /** @var string название компонента базы данных `information_schema` */
    public $componentDbInfo = 'dbInfo';

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
        $componentDbInfo = $this->componentDbInfo;
        /** @var Connection $db */
        $db = \Yii::$app->$componentDbInfo;
        /** @var Connection $db */
        $dbMain = \Yii::$app->db;
        $dbName = $this->getDB($dbMain->dsn);

        $t = InnoDbTable::findOne(['NAME' => $dbName . '/' . $this->name]);
        $cols = InnoDbColumn::find()->where(['TABLE_ID' => $t['TABLE_ID']])->asArray()->all();

        if (empty($this->options)) {
            $this->options = [];
        }

        $this->columns = $this->addColumns($this->columns, $cols);
    }

    private function addColumns($thisColumns, $dbColumns)
    {
        $rows = [];
        foreach ($dbColumns as $c) {
            $c1 = $this->hasColumn($c['NAME']);
            if (is_null($c1)) {
                $type = null;
                switch ($c['MTYPE']) {
                    case 1:
                        $type = 'VARCHAR' . '(' . $c['LEN'] . ')'; break;
                    case 12:
                        $type = 'VARCHAR' . '(' . ($c['LEN'] / 3) . ')'; break;
                    case 2:
                        $type = 'CHAR' . '(' . $c['LEN'] . ')'; break;
                    case 5:
                        $type = 'TEXT'; break;
                    case 6:
                        $type = 'INT' . '(' . $c['LEN'] . ')'; break;
                }

                $rows[] = [
                    'name' => $c['NAME'],
                    'type' => $type,
                ];
            } else {
                $rows[] = $c1;
            }
        }

        return $rows;
    }

    /**
     * Ищет среди колонок указанных пользователем
     */
    private function hasColumn($name)
    {
        foreach ($this->columns as $c) {
            if ($c['name'] == $name) return $c;
        }

        return null;
    }

    /**
     * Возвращает название БД из dsn
     *
     * @param $dsn
     *
     * @return mixed|string|null
     */
    private function getDB($dsn)
    {
        $arr = explode(';', $dsn);
        foreach ($arr as $i) {
            $arr2 = explode('=', $i);
            if ($arr2[0] == 'dbname') return $arr2[1];
        }

        return null;
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