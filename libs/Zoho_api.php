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

    CONST BURL_ACCOURN = "https://accounts.zoho.eu/";

    CONST BURL_CRM = "https://crm.zoho.eu/crm/v2/";

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
            '21'=>  'Неверный ключ достпа. Проверьте файл конфигурации api_zoho.php !',
            '22'=>  'Ошибка в запросе поиска лида по номеру телефона!',
            '23'=>  'Не корректно введен id лида',
            '24'=>  'Не корретно обработан запрос на создание лида',
            '25'=>  'Не корретно обработан запрос на конвертацию лида',
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
            'city'=> 'Город',
            'company' => 'Компания',
        ];
    }

    /**
     * Обязательные поля
     *
     * @return array
     */
    public static function wron_attr()
    {
        return ['phone','name'];
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
        if (!$this->error) $this->test_key_only();
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
            return $this->model[$name];
        }
    }


    /**
     * Делаем запрос
     *
     * @retunr bool
     */
    private function q($url,$data=[],$type = 'POST',$header_post=FALSE)
    {

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $url);

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,TRUE);

        if ($type == 'PUT') {
            if ($header_post) {
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, ['Authorization: '.$this->access_key,"Content-Type: application/json" ]);
            }
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($this->ch, CURLOPT_POSTFIELDS,http_build_query($data));

        }

        if ($type == 'POST') {
            if ($header_post) {
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST,"POST");
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, ['Authorization: '.$this->access_key,"Content-Type: application/json","Cache-Control: no-cache"]);
            }
            curl_setopt($this->ch, CURLOPT_POST, TRUE);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        }
        if ($type == 'GET') {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, ['Authorization: '.$this->access_key,"Cache-Control: no-cache" ]);
        }

        $result = curl_exec($this->ch);

        //file_put_contents("c:\\OSPanel\\domains\\test\\roistat\\curl.txt",print_r($url,TRUE), FILE_APPEND | LOCK_EX );
        //file_put_contents("c:\\OSPanel\\domains\\test\\roistat\\curl.txt","\n".print_r($result,TRUE)."\n\n", FILE_APPEND | LOCK_EX );

        curl_close($this->ch);

        return $result;
    }


    /**
     * Попытка постучаться по старому ключу
     */
    private function test_key_only()
    {
        if (file_exists(__DIR__."/templ_key.php")) {
            $this->access_key = file_get_contents(__DIR__."/templ_key.php", FILE_USE_INCLUDE_PATH);
            $this->test_key();
        } else {
            $this->reqGet_token();
        }

    }

    /**
     * Тест ключа, для организации и хранения ключа
     */
    private function test_key()
    {
        $result = $this->q(self::BURL_CRM."org",[],"GET");
        $result = json_decode($result, TRUE);

        //если не получаем данные, значит ключ проэкспарился, нужно получить новый
        if ($result['status'] && $result['status']=='error') {
            $this->reqGet_token();
        }
    }


    /**
     * Получения ключа доступа
     *
     * @return bool
     */
    private function reqGet_token()
    {
        $postData = [
            'SCOPE' => "ZohoCRM/crmapi",
            'DISPLAY_NAME'=>'Zoho_lib',
            'EMAIL_ID' => $this->config['client_email'],
            'PASSWORD' => $this->config['client_password'],
        ];

        $result = $this->q(self::BURL_ACCOURN."apiauthtoken/nb/create",$postData,"POST");

        $arr = explode("\n",$result);
        $result = explode("=",$arr[2]);
        if (explode("=",$arr[3])[1] == 'TRUE') {
            $this->set_error(0);
            $this->access_key =  $result[1];
            file_put_contents(__DIR__."/templ_key.php",$this->access_key);
        }
        $this->set_error(21);
    }


    /**
     * Поиск лида по номеру телеофна
     */
    public function find_lid_phone($phone=FALSE)
    {

        $phone = ($phone) ? $phone : $this->phone;

        $search = '';
        if ($phone)
        {
            $search= "?&fields=Phone";
        }

        $result = $this->q(self::BURL_CRM."leads".$search,[],"GET");
        $result = json_decode($result,TRUE);
        if (is_array($result) && isset($result['data'])) {
            $this->set_error(0);
            foreach ( $result['data'] as $lid ) {
                if ($lid['Phone'] == $phone) {
                    $this->result = $lid['id'];
                    return $lid['id'];
                }
            }
        } else {
            $this->set_error(22);
        }
        return FALSE;

    }

    /**
     * Создать лид
     */
    public function create_lid()
    {
        $data = json_encode([
            'data' => [
                [
                'Last_Name' => $this->name,
                'Phone' => $this->phone,
                'Email' => $this->email,
                'City'=>$this->city,
                'Company'=>$this->company,
                ],
            ],
        ]);

        $result = $this->q(self::BURL_CRM."leads",$data,"POST",TRUE);

        $_re = json_decode($result,TRUE);

        if ($_re['data'] && $_re['data'][0] && $_re['data'][0]['code'] && $_re['data'][0]['code'] == 'SUCCESS' ) {

            $this->set_error(0);
            return TRUE;
        } else {
            $this->set_error(24);
        }

    }

    /**
     * Конвертировать лид в сделку
     */
    public function convert_deal_to_lid($lid_id = FALSE)
    {
        $lid_id = ($lid_id) ? $lid_id : $this->find_lid_phone();
        if (is_numeric($lid_id)) {

            $data = [
                'data' => [
                    [
                    "overwrite" => true,
                    "notify_lead_owner" => false,
                    "notify_new_entity_owner" => false,
                    "Accounts" => "144263000000116152", //ID контрагента toDo вывести в dropDown list
                    "Contacts" => $lid_id, //ID контакта из справочника
                    "Deals" => [
                        "Deal_Name" => "test",
                        "Stage" => "Stage_0",
                        "Amount"=> 56.6,
                        ],
                    ],
                ],
            ];
            
            $result = $this->q(self::BURL_CRM."Leads/".$lid_id."/actions/convert",json_encode($data),"POST",true);
            $result = json_decode($result,TRUE);

            if ($result['status'] && $result['status'] == "error") {
                $this->set_error(25);
                return FALSE;
            } else {
                $this->set_error( 0);
                return TRUE;
            }

        }
        $this->set_error(23);
        return FALSE;
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