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
						<dt>Repeat report generation</dt>
						<dd>
							Every <input id="repeat_in_days" class="form-control" style="display: inline-block; width: 60px;" type="text" value="<?= (int) $model->repeat_in_days ?>"/> day(s) <input type="button" id="repeat_in_days_submit" value="Ok" class="btn btn-primary">
							<p class="text-muted">Value '0' means disbled repetition.</p>
						</dd>
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
			$.ajax( '/ajax/report-info', {
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
		$( '#repeat_in_days_submit' ).click( function () {
			var repeat_in_days = parseInt( $( '#repeat_in_days' ).val() );
			console.log( repeat_in_days );
			if ( isNaN( repeat_in_days ) || repeat_in_days < 0 )
			{
				alert( 'Incorrect days value' );
				return;
			}

			$.ajax( '/ajax/set-interval', {
				data   : {'id': '<?= $model->filename ?>', 'value': repeat_in_days},
				success: function () {
					alert( 'Interval set successfully' );
				},
				error  : function () {
					alert( 'Something went wrong' );
				}
			} );
		} );

		if ( status == <?= Reports::STATUS_FINISHED ?> )
			return;

		delayedRecursiveUpdater();
	} );
</script>