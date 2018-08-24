<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<main>
    <?= Yii::$app->view->renderFile('@app/views/layouts/header.php'); ?>

    <div class="container">
        <?= $content ?>
    </div>

    <?= Yii::$app->view->renderFile('@app/views/layouts/footer.php'); ?>
</main>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
