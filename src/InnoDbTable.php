<?php

namespace iAvatar777\services\DbTable;

/**
 * Created by PhpStorm.
 * User: Святослав
 * Date: 04.10.2021
 * Time: 18:06
 */

class InnoDbTable extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->dbInfo;
    }

    public static function tableName()
    {
        return 'INNODB_TABLES';
    }
}