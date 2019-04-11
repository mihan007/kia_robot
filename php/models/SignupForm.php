<?php
namespace app\models;

use yii\base\Model;
use app\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $company_id;
    public $role;

    /**
     * @var User $_user
     */
    private $_user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'Данное имя пользователя уже занято.', 'except' => 'editUser'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'Данный email адрес уже занят.', 'except' => 'editUser'],

            ['password', 'required', 'except' => 'editUser'],
            ['password', 'string', 'min' => 6, 'except' => 'editUser'],

            ['role', 'required', 'on' => 'dealerSignup'],
            ['role', 'required', 'on' => 'editUser'],

            ['company_id', 'required', 'on' => 'dealerSignup'],
            ['company_id', 'required', 'on' => 'editUser'],

            ['password', 'safe', 'on' => 'editUser']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Имя',
            'password' => 'Пароль',
            'role' => 'Роль',
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->company_id = $this->company_id;

        return $user->save() ? $user : null;
    }

    public function initByUser(User $user)
    {
        $this->username = $user->username;
        $this->email = $user->email;
        $this->company_id = $user->company_id;
        $this->_user = $user;
        if (in_array('leadManager', $user->getRoles())) {
            $this->role = User::ROLE_LEAD_MANAGER;
        } else {
            $this->role = User::ROLE_MANAGER;
        }
    }

    public function saveWithAssignedUser()
    {
        if (!$this->validate()) {
            return null;
        }

        $this->_user->username = $this->username;
        $this->_user->email = $this->email;
        if (strlen($this->password)>0) {
            $this->_user->setPassword($this->password);
        }
        $this->_user->generateAuthKey();
        $this->_user->company_id = $this->company_id;

        return $this->_user->save() ? $this->_user : null;
    }
}