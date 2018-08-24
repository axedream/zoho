<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
		//'css/bootstrap.min.css',
    ];
    public $js = [
        //'js/jquery.min.js',
		//'js/bootstrap.min.js',
    ];
    public $depends = [

        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',

    ];
}
