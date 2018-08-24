<?php
namespace app\models;

use \yii\base\Model;

class Lid extends Model
{
    public $name;
    public $phone;
    public $email;
    public $price;
    public $comment;


    public function rules()
    {
        return [
            [['name','phone','email','comment'], 'string', 'max' => 255],
            [['name','price','phone'],'required'],
            [['price'], 'safe'],
            // тут определяются правила валидации
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Имя клиента',
            'phone'=> 'Телефон клиента',
            'email'=> 'Электронный адрес клиента',
            'price'=> 'Стоимость сделки',
            'comment'=>'Дополнительный коментарий',
        ];
    }
}