<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "color_preferences".
 *
 * @property int $id
 * @property string $model_name
 * @property string $model_value
 * @property string $colors
 */
class ColorPreferences extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'color_preferences';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['model_name', 'model_value', 'colors'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_name' => 'Модель',
            'model_value' => 'Model Value',
            'colors' => 'Цвета',
            'colorsReadable' => 'Цвета',
        ];
    }

    public function getColorsReadable()
    {
        return str_replace(",", ", ", $this->colors);
    }
}
