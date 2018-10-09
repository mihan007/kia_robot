<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "task_run_screenshot".
 *
 * @property int $id
 * @property int $task_run_id
 * @property string $name
 * @property string $filepath
 * @property int $created_at
 * @property int $updated_at
 *
 * @property TaskRun $taskRun
 */
class TaskRunScreenshot extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task_run_screenshot';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_run_id', 'created_at', 'updated_at'], 'integer'],
            [['name', 'filepath'], 'string', 'max' => 255],
            [['task_run_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaskRun::className(), 'targetAttribute' => ['task_run_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_run_id' => 'Task Run ID',
            'name' => 'Name',
            'filepath' => 'Filepath',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTaskRun()
    {
        return $this->hasOne(TaskRun::className(), ['id' => 'task_run_id']);
    }
}
