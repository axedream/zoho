<?php

return [
    'adminEmail' => 'axe_dream@list.ru',                    //емали для которого проихводится тотравка

    'basic_url' => HTTP.'://'.$_SERVER['HTTP_HOST'].'/',    //базовый URL для веб страниц
    'basic_dir_files' => 'upload',                          //папка  хранения загружаемых файлов
    'basic_url_console' => 'http://test.local/',               //хост для локальных задач из консоли

    'messages' => require __DIR__ .'\messages.php',
    'time_auth'=> 60 * 60 * 24,
];
