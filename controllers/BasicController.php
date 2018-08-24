<?php
namespace app\controllers;


use yii\web\Controller;
use Yii;
use yii\web\Response;

class BasicController extends Controller
{
    /**
     * Сообщение об ошибке
     *
     * @var mixed
     */
    public $msg;
    /**
     * Наличие ошибки
     *
     * @var string
     */
    public $error;

    /**
     * Код ошибки
     *
     * @var int
     */
    public $error_type = 0;


    /**
     * Данные к выдаче
     *
     * @var array
     */
    public $data;

    /**
     * Работа с ссессиями
     *
     * @var string
     */
    public $session;

    /**
     * Группы текущего пользователя [array]
     *
     * @var string
     */
    public $sUser_ip = FALSE;


    /**
     * Данные о пришедшем пользователе
     */
    public function getUsersData()
    {
        $this->sUser_ip = Yii::$app->request->userIP;
    }

    /**
     * Первичная инициализация для всех контроллеров
     */
    public function init()
    {
        if (isset(Yii::$app->session)) {
            $this->session = Yii::$app->session;
            if (!$this->session->isActive) {
                $this->session->open();
            }        }

        $this->getUsersData();
    }

    //---------------------------------------------------- AJAX ----------------------------------------//
    /**
     * Стандартная выдача сообщений
     *
     * @return array
     */
    public function out()
    {
        return ['error'=>$this->error, $this->error_type,'msg'=>$this->msg, 'data'=> ($this->error=='no') ? $this->data : '' ];
    }

    /**
     * Базовая инициализация
     */
    public function init_ajax()
    {
        $this->error = 'yes';
        $this->msg = Yii::$app->params['messages']['user']['error']['params'];
        Yii::$app->response->format = Response::FORMAT_JSON;
    }
    //---------------------------------------------------- END AJAX ----------------------------------------//


}