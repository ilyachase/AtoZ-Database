<?php

/**
 * @var $this yii\web\View
 * @var $model \app\models\activerecord\Reports|null
 */

use app\models\activerecord\Reports;
use yii\helpers\Html;

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
					<dd id="status"><?= $model->getStatusHtml() ?></dd>
					<dt>Report rows count</dt>
					<dd id="count"><?= $model->count ?></dd>
					<div id="results_wrap"<?php if ( $model->status !== Reports::STATUS_FINISHED ): ?> style="display: none"<?php endif; ?>>
						<dt>Result csv</dt>
						<dd><?= Html::a( 'Download', [ '/site/report/', 'id' => $model->filename, 'action' => 'download' ] ) ?></dd>
						<dt>API results endpoint</dt>
						<dd><?= Html::a( 'Results', [ '/api/report', 'id' => $model->filename ] ) ?></dd>
					</div>
				</dl>
			<?php endif; ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	var status = <?= $model->status ?>;

	function delayedRecursiveUpdater()
	{
		if ( status == <?= Reports::STATUS_FINISHED ?> )
			return;

		setTimeout( function () {
			$.ajax( '/api/report-info', {
				data   : {'id': '<?= $model->filename ?>'},
				success: function ( data ) {
					status = data.status;
					fillData( data );
					delayedRecursiveUpdater();
				}
			} );
		}, 3000 );
	}

	function fillData( data )
	{
		$( '#status' ).html( data.status_html );
		$( '#count' ).html( data.count );

		if ( status == <?= Reports::STATUS_FINISHED ?> )
		{
			$( '#results_wrap' ).show();
		}
	}

	$( function () {
		if ( status == <?= Reports::STATUS_FINISHED ?> )
			return;

		delayedRecursiveUpdater();
	} );
</script>