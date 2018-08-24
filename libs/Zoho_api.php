<?php
namespace app\libs;

/**
 * Библиотека для работы с Zoho
 *
 * Class Math
 * @package app\libs
 */
class Zoho_api
{

    /**
     * Соединение
     *
     * @var mixed
     */
    private $ch;


    /**
     * Ключ доступа
     *
     * @var string
     */
    private $access_key;

    /**
     * Результат операции
     *
     * @var int
     */
    private $result;

    /**
     * Признак наличие ошибки
     *
     * @var bool
     */
    public $error;

    /**
     * Описание ошибки
     *
     * @var string
     */
    public $error_text;

    /**
     * Номер ошибки
     *
     * @var int
     */
    public $error_code;

    /**
     * Модель передаваемых данных
     *
     * @var object
     */
    private $model;

    /**
     * Конфигурация ключа
     *
     * @var array
     */
    private $config;

    /**
     * Задаем начальные параметры
     *
     * Math constructor.
     * @param bool $a
     * @param bool $b
     */
    public function __construct($config = FALSE,$model = FALSE)
    {
        $this->loading_basic_data($config,$model);
    }

    /**
     * Типы и описание ошибок
     *
     * @return array
     */
    public static function error_type()
    {
        return [
            '0' =>  'Нет ошибок',
            '1' =>  'Не верный/указан client_id/client_secret',
            '2' =>  'Нет данных в модели',
            '10'=>  'Исключительная ошибка',
            '21'=>  'Неверный ключ достпа. Проверьте файл конфигурации api_zoho.php !'
        ];
    }

    /**
     * Атрибуты
     *
     * @return array
     */
    public static function attr()
    {
        return [
            'name' => 'Имя',
            'phone'=> 'Телефон',
            'email'=> 'E-mail',
            'comment'=> 'Коментарий',
            'price' => 'Стоимость',
        ];
    }

    /**
     * Обязательные поля
     *
     * @return array
     */
    public static function wron_attr()
    {
        return ['name','price','phone'];
    }


    /**
     * Загрузка основных данных (если нужно создать DI то можно загрузить будет отдельно, не при инициализации)
     * по факту инциализируем объект независимо еще раз
     *
     * @param bool $config
     * @param bool $model
     */
    public function loading_basic_data($config = FALSE,$model = FALSE)
    {
        $this->init_error();
        if (!$this->error) $this->init_config($config);
        if (!$this->error) $this->init_data($model);
        if (!$this->error) $this->reqGet_token();
    }

    /**
     * Инициализируем ошибки
     */
    public function init_error()
    {
        $this->reuslt = 0;

        $this->error = FALSE;
        $this->error_code = 0;
        $this->error_test = self::error_type()[0];
    }

    /**
     * Выставляем ошибку
     *
     * @param int $id
     */
    private function set_error($id=0)
    {
        if ($id) {
            $this->result = 0;
            $this->error = TRUE;
        } else {
            $this->error = FALSE;
        }

        if (in_array($id,array_keys(self::error_type()))) {
            $this->error_code = $id;
        } else {
            $this->error_code = 10;
        }

        $this->error_text = self::error_type()[$this->error_code];


    }

    /**
     * Конфигурируем
     *
     * @param $config
     */
    public function init_config($config)
    {
        if ($config && !empty($config)) {
            $this->config = $config;
        }

        if ($this->config && is_array($this->config) && !empty($this->config['client_email']) && !empty($this->config['client_password']) ) {
            $this->set_error(0);
        } else {
            $this->set_error(1);
        }
    }

    /**
     * Тестируем переданные данные на ошибки
     *
     * @param $model
     */
    public function init_data($model) {
        //ошибка есть 1, нужно доказать обратное 0
        $error_key = 1;

        //если пришел массив, доказываем
        if (is_array($model) && count($model)>0) {
            if (count(self::wron_attr()) >0) {
                $k = 0;
                foreach (self::wron_attr() as $wron) {
                    if (isset($model[$wron]) && trim($model[$wron])!='') {
                        $k++;
                    }
                }

                if ($k == count(self::wron_attr())) {
                    $error_key = 0;
                }
            //если нет атрибутов для проверки
            } else {
                $error_key = 0;
            }
        }
        //если пришла модель, доказываем
        if (is_object($model) ) {
            if (count(self::wron_attr()) >0) {
                $k = 0;
                foreach (self::wron_attr() as $wron) {
                    if (property_exists($model,$wron) && trim($model->$wron)!='') {
                        $k++;
                    }
                }
                if ($k == count(self::wron_attr())) {
                    $error_key = 0;
                }
            } else {
                $error_key = 0;
            }
        }

        //подбиваем результат
        if (!$error_key) {
            $this->set_error(0);
            $this->load_data($model);
        } else {
            $this->set_error(2);
        }
    }

    /**
     * Предварительная обработка входящих данных
     *
     * @param $model
     * @param $type
     */
    private function load_data($model)
    {
        foreach (array_keys(self::attr()) as $attr) {
            if (is_array($model)) {
                $this->model[$attr] = trim($model[$attr]);
            }
            if (is_object($model)) {
                $this->model[$attr] = trim($model->$attr);
            }
        }
    }

    /**
     * Записываем свойства данных
     *
     * @param $name
     * @param $value
     */
    public function __set($name,$value)
    {
        //для модели
        if (in_array($name,array_keys(self::attr()))) {
            $this->model['name'] = $value;
        }
    }

    /**
     * Читаем свойство данных
     *
     * @param $name
     */
    public function __get($name)
    {

        //для модели
        if (in_array($name,array_keys(self::attr()))) {
            return $this->model['name'];
        }
    }


    /**
     * Получения ключа доступа
     *
     * @return bool
     */
    private function reqGet_token()
    {
        $url = "https://accounts.zoho.eu/apiauthtoken/nb/create";
        $this->ch = curl_init();

        $postData = [
            'SCOPE' => "ZohoCRM/crmapi",
            'DISPLAY_NAME'=>'Zoho_lib',
            'EMAIL_ID' => $this->config['client_email'],
            'PASSWORD' => $this->config['client_password'],
        ];

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postData);

        $result = curl_exec($this->ch);
        curl_close($this->ch);
        $arr = explode("\n",$result);
        $result = explode("=",$arr[2]);
        if (explode("=",$arr[3])[1] == 'TRUE') {
            $this->set_error(0);
            $this->access_key =  $result[1];
        }
        $this->set_error(21);
    }


    /**
     * Поиск лида по номеру телеофна
     */
    private function find_lid_phone()
    {

    }

    /**
     * Создать лид
     */
    private function create_lid()
    {

    }

    /**
     * Обновить лид
     */
    private function update_lid()
    {

    }

    /**
     * Возвращяем результат
     *
     * @return mixed
     */
    public function get_result()
    {
        if (!$this->error) {
            return $this->result;
        }
        return FALSE;
    }




}