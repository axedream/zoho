<?php
namespace app\models;

use \yii\base\Model;

class Lid extends Model
{
    public $name;
    public $phone;
    public $email;
    public $city;
    public $company;


    public function rules()
    {
        return [
            [['name','phone','email','company','city'], 'string', 'max' => 255],
            [['phone','name'],'required'],
            // тут определяются правила валидации
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Имя клиента',
            'phone'=> 'Телефон клиента',
            'email'=> 'Электронный адрес клиента',
            'city'=> 'Город',
            'company'=>'Компания',
        ];
    }
}