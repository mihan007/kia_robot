<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "storage_session".
 *
 * @property int $id
 * @property string $started_at
 * @property string $finished_at
 */
class StorageSession extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'storage_session';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['started_at', 'finished_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'started_at' => 'Started At',
            'finished_at' => 'Finished At',
        ];
    }
}
