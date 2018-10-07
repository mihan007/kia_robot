<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "task".
 *
 * @property int $id
 * @property string $model_name
 * @property string $model_value
 * @property string $manufacture_code_name
 * @property string $manufacture_code_value
 * @property string $color_inside_name
 * @property string $color_inside_value
 * @property string $color_outside_name
 * @property string $color_outside_value
 * @property int $amount
 * @property string $created
 * @property string $updated
 * @property string $deleted
 */
class Task extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount'], 'integer'],
            [['deleted'], 'safe'],
            [['model_name', 'model_value', 'manufacture_code_name', 'manufacture_code_value', 'color_inside_name', 'color_inside_value', 'color_outside_name', 'color_outside_value'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_name' => 'Model Name',
            'model_value' => 'Model Value',
            'manufacture_code_name' => 'Manufacture Code Name',
            'manufacture_code_value' => 'Manufacture Code Value',
            'color_inside_name' => 'Color Inside Name',
            'color_inside_value' => 'Color Inside Value',
            'color_outside_name' => 'Color Outside Name',
            'color_outside_value' => 'Color Outside Value',
            'amount' => 'Amount',
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
        ];
    }
}
