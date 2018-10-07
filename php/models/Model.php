<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "model".
 *
 * @property int $id
 * @property string $name
 * @property string $value
 *
 * @property ColorInside[] $colorInsides
 * @property ColorOutside[] $colorOutsides
 * @property ManufactureCode[] $manufactureCodes
 */
class Model extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'model';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'value'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'value' => 'Value',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getColorInsides()
    {
        return $this->hasMany(ColorInside::className(), ['model_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getColorOutsides()
    {
        return $this->hasMany(ColorOutside::className(), ['model_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManufactureCodes()
    {
        return $this->hasMany(ManufactureCode::className(), ['model_id' => 'id']);
    }
}
