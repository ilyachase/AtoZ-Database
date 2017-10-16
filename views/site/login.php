<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login text-center">
	<h1><?= Html::encode( $this->title ) ?></h1>

	<?php $form = ActiveForm::begin(); ?>

	<?= $form->field( $model, 'password', [ 'options' => [ 'style' => 'width: 250px; margin: 0 auto;' ] ] )->passwordInput() ?>

	<div class="form-group">
		<div class="col-lg-12">
			<?= Html::submitButton( 'Login', [ 'class' => 'btn btn-primary', 'name' => 'login-button' ] ) ?>
		</div>
	</div>

	<?php ActiveForm::end(); ?>
</div>
