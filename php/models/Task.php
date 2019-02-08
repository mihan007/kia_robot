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
 * @property boolean $more_auto
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $company_id
 * @property int $user_id
 *
 * @property Company $company
 * @property User $user
 * @property TaskRun[] $taskRuns
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
            [['amount', 'more_auto'], 'integer'],
            [['amount'], 'required'],
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
            'model_name' => 'Модель',
            'model_value' => 'Модель',
            'manufacture_code_name' => 'Код производителя',
            'manufacture_code_value' => 'Код производителя',
            'color_inside_name' => 'Цвет Салон',
            'color_inside_value' => 'Цвет Салон',
            'color_outside_name' => 'Цвет Кузов',
            'color_outside_value' => 'Цвет Кузов',
            'amount' => 'Количество',
            'more_auto' => 'Если авто с нужным кодом производителя недостаточно, то заказывать альтернативные',
            'created_at' => 'Дата создания',
            'updated_at' => 'Updated',
            'deleted_at' => 'Deleted',
        ];
    }

    public function getDescription()
    {
        $result = [
            'Модель: '.$this->model_name,
            'Код производителя: '.$this->manufacture_code_name,
            'Цвет Салона: '.$this->color_inside_name,
            'Цвет Кузова: '.$this->color_outside_name,
            'Количество: '.$this->amount
        ];

        return implode('<br>', $result);
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getTaskRuns()
    {
        return $this->hasMany(TaskRun::className(), ['task_id' => 'id']);
    }

    public function getAmountOrdered()
    {
        $ordered = 0;
        foreach ($this->taskRuns as $taskRun) {
            $ordered += $taskRun->amount_ordered;
        }

        return $ordered;
    }
}
