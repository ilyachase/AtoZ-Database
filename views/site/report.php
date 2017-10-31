<?php

/**
 * @var $this yii\web\View
 * @var $model \app\models\activerecord\Reports|null
 */

$this->title = 'Report';
?>
<div class="report-wrap">
	<div class="panel panel-default keyword-panel">
		<div class="panel-heading">
			<h3 class="panel-title">Report details</h3>
		</div>
		<div class="panel-body">
			<?php if ( !$model ): ?>
				Sorry, report with such ID not found.
			<?php else: ?>
				<dl>
					<dt>Report ID</dt>
					<dd><?= $model->filename ?></dd>
					<dt>Email for report</dt>
					<dd><?= $model->email ?></dd>
					<dt>Report working status</dt>
					<dd><?= $model->getStatusHtml() ?></dd>
					<dt>Report rows count</dt>
					<dd><?= $model->count ?></dd>
				</dl>
			<?php endif; ?>
		</div>
	</div>
</div>