<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\validators\EmailValidator;

/**
 * This is the model class for table "company".
 *
 * @property int $id
 * @property string $name
 * @property string $kia_login
 * @property string $kia_password
 * @property string $notification_email
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class Company extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_DELETED = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['kia_login', 'kia_password'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'status' => 'Статус',
            'statusLabel' => 'Статус',
            'createdAt' => 'Добавлен',
            'kia_login' => 'Логин от сайта Киа',
            'kia_password' => 'Пароль от сайта Киа',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getStatusLabel()
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return 'Активна';
            case self::STATUS_INACTIVE:
                return 'Неактивна';
            case self::STATUS_DELETED:
                return 'Удалена';
        }
    }

    public function getCreatedAt()
    {
        return date('Y-m-d H:i', $this->created_at);
    }

    public function setNotificationEmail($originalEmail)
    {
        $email = trim($originalEmail);
        if (strlen($email) == 0) {
            return true;
        }
        $emails = explode(',', $email);
        $result = [];
        $bad = [];
        foreach ($emails as $email) {
            $emailNormalized = trim($email);
            $validator = new EmailValidator();
            if ($validator->validate($emailNormalized, $error)) {
                $result[] = $email;
            } else {
                $bad[] = $email;
            }
        }
        if (sizeof($bad) == 0) {
            $this->notification_email = implode(',', $result);
            return true;
        } else {
            $this->notification_email = $originalEmail;
            return $bad;
        }
    }
}
