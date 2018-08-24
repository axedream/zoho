<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;

$script = <<<JS
    // -------------------------- basic --------------------------------//
    this_host = window.location.protocol + "//" + window.location.hostname;
    $(function(){

        function remove_all_class(){
            $("#show_result div.modal-header").removeClass('bg-success');
            $("#show_result div.modal-header").removeClass('bg-danger');
        }

        function get_all_data(){
            var data= new Object();
            $.each($('#form_request input'),function(t,val){
                var k = $(val).attr('name');
                var v = $("[name='"+k+"']").val()
                data[k] = v ;
            })
            return data;
        }
        
        // ---------------------- afrer request -----------------------//
        function good_request() {
            $("#show_result").modal('show');
            remove_all_class();
            $("#show_result div.modal-header").addClass('bg-success');
            $("#test_result").text('Успех. Лид создан!');
        }

        function bad_request(text) {
            $("#show_result").modal('show');
            remove_all_class();
            $("#show_result div.modal-header").addClass('bg-danger');
            $("#test_result").text(text);
        }

        // ---------------------- ajaxQuery --------------------------//
        function before_send_validate(){
            var out = 0;
            $.ajax({
                url: this_host + "/site/validate_form_lid",
                type: 'POST',
                dataType: 'JSON',
                data: get_all_data(),
                cache: false,
                async:false,
                success: function (msg) {
                    if (msg.length == 0) {
                        out = true;
                    } else {
                        bad_request('Не верно заполнены поля. Измените/заполните значения полей и повторите попытку');
                        out = false;
                    }
                }
            });
            return out;
        }
        
        
        
        function send_request(){
            $.ajax({
                url: this_host + "/site/create_lid",
                type: 'POST',
                dataType: 'JSON',
                data: get_all_data(),
                cache: false,
                success: function (msg) {
                    if (msg.error=="no") {
                        good_request();
                    } else {
                        bad_request(msg.msg);
                    }
                }
            });
        }

        //----------------------- buttons -------------------------//
        $("#button_send").on('click',function(e){
            e.preventDefault();
            if (before_send_validate()){
                send_request();    
            }
            return false;
        });
        $("#show_result_close").on('click',function(){ $("#show_result").modal('hide'); })
    });

JS;

$this->registerJs($script,yii\web\View::POS_READY);

?>

<?php
$model = $this->context->model;

$form = ActiveForm::begin([
    'id' => 'form_request',
    'enableClientValidation' => false,
    'enableAjaxValidation' => true,
    'validationUrl'=>Url::toRoute('site/validate_form_lid'),
    'options' => ['class' => 'form-horizontal'],
])
?>
<div class="form-row">
    <div class="col-md-12">
        <div class="col-md-3">
            <div class="t">
                <?= $form->field($model, 'name') ?>
            </div>
        </div>

        <div class="col-md-3">
            <div class="t">
                <?= $form->field($model, 'phone') ?>
            </div>
        </div>

        <div class="col-md-3">
            <div class="t">
                <?= $form->field($model, 'email') ?>
            </div>
        </div>

        <div class="col-md-3">
            <div class="t">
                <?= $form->field($model, 'price') ?>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="col-md-12">
            <div class="t">
                <?= $form->field($model, 'comment') ?>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <?= Html::submitButton('Создать лид', ['class' => 'btn btn-primary','id'=> 'button_send']) ?>
    </div>

</div>

<?php ActiveForm::end() ?>


<?php
    Modal::begin([
        'options'=> [
                'id'=>'show_result',
            ],
        'header'=>'Результат запроса',
        'footer'=>'<span class="btn btn-default" id="show_result_close">Закрыть</span>'
    ]);
?>
<div id="test_result" style="font-size: 22px; font-weight: bold;"></div>
<?php Modal::end(); ?>

<style type="text/css">
    .t {
        margin-right: 10px;
    }
</style>