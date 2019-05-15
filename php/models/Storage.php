<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "storage".
 *
 * @property int $id
 * @property int $storage_session_id
 * @property string $model
 * @property string $manufacture_code
 * @property string $description
 * @property string $color_outside
 * @property string $color_inside
 * @property int $year
 * @property string $storage_code
 * @property int $available
 * @property int $reserved
 * @property int $page
 * @property string $created_at
 */
class Storage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'storage';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['storage_session_id', 'year', 'available', 'reserved', 'page'], 'integer'],
            [['created_at'], 'safe'],
            [['model', 'manufacture_code', 'description', 'color_outside', 'color_inside', 'storage_code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'storage_session_id' => 'Storage Session ID',
            'model' => 'Model',
            'manufacture_code' => 'Manufacture Code',
            'description' => 'Description',
            'color_outside' => 'Color Outside',
            'color_inside' => 'Color Inside',
            'year' => 'Year',
            'storage_code' => 'Storage Code',
            'available' => 'Available',
            'reserved' => 'Reserved',
            'page' => 'Page',
            'created_at' => 'Добавлена',
        ];
    }
}
