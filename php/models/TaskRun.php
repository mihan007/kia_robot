<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "task_run".
 *
 * @property int $id
 * @property int $task_id
 * @property string $model_name
 * @property string $model_value
 * @property string $manufacture_code_name
 * @property string $manufacture_code_value
 * @property string $color_inside_name
 * @property string $color_inside_value
 * @property string $color_outside_name
 * @property string $color_outside_value
 * @property int $amount
 * @property int $amount_ordered
 * @property int $created_at
 * @property int $updated_at
 * @property int $notified
 * @property int $push_notified
 *
 * @property Task $task
 * @property TaskRunScreenshot[] $taskRunScreenshots
 * @property Company $company
 * @property User $user
 * @property boolean $tooOld
 */
class TaskRun extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task_run';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_id', 'amount', 'created_at', 'updated_at'], 'integer'],
            [['model_name', 'model_value', 'manufacture_code_name', 'manufacture_code_value', 'color_inside_name', 'color_inside_value', 'color_outside_name', 'color_outside_value'], 'string', 'max' => 255],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::className(), 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'Task ID',
            'model_name' => 'Модель',
            'model_value' => 'Модель',
            'manufacture_code_name' => 'Код производителя',
            'manufacture_code_value' => 'Код производителя',
            'color_inside_name' => 'Цвет Салон',
            'color_inside_value' => 'Цвет Салон',
            'color_outside_name' => 'Цвет Кузов',
            'color_outside_value' => 'Цвет Кузов',
            'amount' => 'План, шт.',
            'amount_ordered' => 'Факт, шт.',
            'created_at' => 'Дата завершения',
            'updated_at' => 'Updated At',
        ];
    }

    public static function fieldsToSelect()
    {
        return [
            'id',
            'task_id',
            'model_name',
            'model_value',
            'manufacture_code_name',
            'manufacture_code_value',
            'color_inside_name',
            'color_inside_value',
            'color_outside_name',
            'color_outside_value',
            'amount',
            'amount_ordered',
            'created_at',
            'updated_at',
            'push_notified',
            'company_id'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::className(), ['id' => 'task_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTaskRunScreenshots()
    {
        return $this->hasMany(TaskRunScreenshot::className(), ['task_run_id' => 'id']);
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getTooOld()
    {
        $createdAtTimestamp = strtotime($this->created_at);
        $week = 7 * 3600 * 24;

        return time() - $createdAtTimestamp > $week;
    }
}
