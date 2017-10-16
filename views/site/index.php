<?php

/* @var $this yii\web\View */

$this->registerJsFile(
	'@web/js/index.js',
	[ 'depends' => [ \yii\web\JqueryAsset::className() ] ]
);

$this->title = 'Searcher';
?>
<div class="search-wrap">
	<div class="panel panel-default panel-search">
		<div class="panel-body">
			<button type="button" id="search" class="btn btn-success"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Search</button>
			<div class="count-wrap">
				<p>Your Count<br><span id="count">0</span></p>
			</div>
			<button type="button" id="update_count" class="btn btn-default"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Update Count</button>
			<br/>
			<p class="text-center"><a href="#" id="clear_search">Clear Search</a></p>
		</div>
	</div>
	<div class="panel panel-default keyword-panel">
		<div class="panel-heading">
			<h3 class="panel-title">Keyword / Business Type search</h3>
		</div>
		<div class="panel-body">
			<form id="keyword_form">
				<label for="input_keyword">Enter Keyword / Business Type</label>
				<div class="form-inline">
					<input type="text" class="form-control" id="input_keyword" placeholder="keyword">
					&nbsp;&nbsp;<i class="glyphicon glyphicon-refresh gly-spin" id="loader" style="display: none;"></i>
				</div>
				<br/>
				<label>Select Keyword / Business Type(s)</label>
				<ul class="list-group keywords-list" id="suggested_keywords">

				</ul>
				<label>Select Keyword / Business Type(s)</label>
				<ul class="list-group keywords-list" id="selected_keywords">
				</ul>
			</form>
		</div>
	</div>
</div>