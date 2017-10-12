<?php

/* @var $this yii\web\View */

$this->title = 'Searcher';
?>
<div class="search-wrap">
	<div class="panel panel-default panel-search">
		<div class="panel-body">
			<button type="button" id="search" class="btn btn-success"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Search</button>
			<div class="count-wrap">
				<p>Your Count<br><span id="count">1000</span></p>
			</div>
			<button type="button" id="update_count" class="btn btn-default"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Update Count</button>
			<br/>
			<p class="text-center"><a href="">Clear Search</a></p>
		</div>
	</div>
	<div class="panel panel-default keyword-panel">
		<div class="panel-heading">
			<h3 class="panel-title">Keyword / Business Type search</h3>
		</div>
		<div class="panel-body">
			<form>
				<div class="form-group">
					<label for="input_keyword">Enter Keyword / Business Type</label>
					<input type="email" class="form-control" id="input_keyword" placeholder="keyword">
				</div>
				<label>Select Keyword / Business Type(s)</label>
				<ul class="list-group">
					<li class="list-group-item">Cras justo odio</li>
					<li class="list-group-item">Dapibus ac facilisis in</li>
					<li class="list-group-item">Morbi leo risus</li>
					<li class="list-group-item">Porta ac consectetur ac</li>
					<li class="list-group-item">Vestibulum at eros</li>
				</ul>
				<label>Select Keyword / Business Type(s)</label>
				<ul class="list-group">
					<li class="list-group-item">Cras justo odio</li>
					<li class="list-group-item">Dapibus ac facilisis in</li>
					<li class="list-group-item">Morbi leo risus</li>
					<li class="list-group-item">Porta ac consectetur ac</li>
					<li class="list-group-item">Vestibulum at eros</li>
				</ul>
			</form>
		</div>
	</div>
</div>