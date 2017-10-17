<?php

/**
 * @var $this yii\web\View
 * @var int $totalrecords
 * @var int $currentPage
 * @var int $totalPages
 * @var array $rows
 */

use yii\helpers\Html;

$this->registerJsFile(
	'@web/js/results.js',
	[ 'depends' => [ \yii\web\JqueryAsset::className() ] ]
);

$this->registerJsFile(
	'@web/js/vendor/url.min.js',
	[ 'depends' => [ \yii\web\JqueryAsset::className() ] ]
);

$this->title = 'Results';
?>
<div class="results-wrap">
	<div class="panel panel-default panel-results">
		<div class="panel-heading">
			<h3 class="panel-title">Search results</h3>
		</div>
		<div class="panel-body">
			<h3><?= $totalrecords ?> results</h3>
			<form class="form-inline" id="page_form">
				<div class="form-group">
					<button type="button" id="prev_page" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span></button>
				</div>
				<div class="form-group">
					<input id="current_page" type="text" class="form-control page-input" value="<?= $currentPage ?>"/>
				</div>
				<div class="form-group">
					<button type="button" id="next_page" class="btn btn-default"><span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span></button>
				</div>
			</form>
			<h5>Page <span id="current_page"><?= $currentPage ?></span> of <span id="total_pages"><?= $totalPages ?></span></h5>
			<table class="table table-bordered table-striped">
				<thead>
				<tr>
					<th>Business Name</th>
					<th>Name</th>
					<th>Address</th>
					<th>City, State</th>
					<th>Phone</th>
					<th>Revenue / Yr</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( $rows as $row ): ?>
					<tr>
						<td><?= Html::a( strip_tags( $row[1] ), [ '/site/details', 'id' => urlencode( $row[0] ) ] ) ?></td>
						<td><?= $row[2] ?></td>
						<td><?= $row[3] ?></td>
						<td><?= $row[4] ?></td>
						<td><?= $row[5] ?></td>
						<td><?= $row[6] ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>