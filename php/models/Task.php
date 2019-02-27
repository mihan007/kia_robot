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
 * @property string $client_name
 * @property int $amount
 * @property boolean $more_auto
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $company_id
 * @property int $user_id
 * @property int $goal
 *
 * @property Company $company
 * @property User $user
 * @property TaskRun[] $taskRuns
 */
class Task extends \yii\db\ActiveRecord
{
    const GOAL_SPECIFIC = 0;
    const GOAL_COMMON = 1;

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
            [['amount', 'more_auto', 'goal'], 'integer'],
            [['amount'], 'required'],
            [['client_name'], 'string', 'max' => 255],
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
            'goal' => 'Цель задачи',
            'goalLabel' => 'Цель задачи',
            'moreAutoLabel' => 'Алтернативы',
            'client_name' => 'Имя клиента',
            'ordered' => 'Заказано'
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

    public function getGoalLabel()
    {
        if ($this->goal == self::GOAL_COMMON) {
            return 'Наполнение склада';
        }

        return 'Конкретные авто';
    }

    public function getMoreAutoLabel()
    {
        return $this->more_auto ? 'Да' : 'Нет';
    }

    public function getOrdered()
    {
        $sql = "SELECT sum(amount_ordered) as ordered FROM task_run WHERE task_id=".$this->id;
        $connection = Yii::$app->getDb();
        $result = $connection->createCommand($sql)->queryOne();

        return isset($result['ordered']) ? $result['ordered'] : 0;
    }
}
