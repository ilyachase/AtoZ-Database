<?php

/**
 * @var $this yii\web\View
 * @var $data stdClass
 */

$this->title = html_entity_decode( $data->Overview[0][1] );

?>
<div class="details-wrap">
	<div class="panel panel-default panel-results">
		<div class="panel-heading">
			<h3 class="panel-title"><?= $data->Overview[0][1] ?></h3>
		</div>
		<div class="panel-body">
			<dl>
				<dt>Business Name</dt>
				<dd><?= $data->Overview[0][1] ?></dd>
				<dt>Website</dt>
				<dd><?= $data->Overview[6][1] ?></dd>
				<dt>Industry Profile</dt>
				<dd>
					<ul>
						<li><strong><?= $data->{'Industry Profile'}[0][0] ?>:</strong><br/><?= $data->{'Industry Profile'}[0][1] ?></li>
						<li><strong><?= $data->{'Industry Profile'}[1][0] ?>:</strong><br/><?= $data->{'Industry Profile'}[1][1] ?></li>
						<li><strong><?= $data->{'Industry Profile'}[2][0] ?>: </strong><?= $data->{'Industry Profile'}[2][1] ?></li>
						<li><strong><?= $data->{'Industry Profile'}[3][0] ?>: </strong><?= $data->{'Industry Profile'}[3][1] ?></li>
						<li><strong><?= $data->{'Industry Profile'}[4][0] ?>: </strong><?= $data->{'Industry Profile'}[4][1] ?></li>
						<li><strong><?= $data->{'Industry Profile'}[5][0] ?>: </strong><?= $data->{'Industry Profile'}[5][1] ?></li>
					</ul>
				</dd>
				<dt>Phone</dt>
				<dd><?= $data->Overview[3][1] ?></dd>
				<dt>City</dt>
				<dd><?= $data->{'Job Postings'}[1][1] ?></dd>
				<dt>State</dt>
				<dd><?= $data->{'Job Postings'}[2][1] ?></dd>
				<?php if ( isset( $data->{'Executive Directory'}[0] ) && isset( $data->{'Executive Directory'}[0][1] ) && count( $data->{'Executive Directory'}[0][1] ) ): ?>
					<dt>Executive Directory</dt>
					<dd>
						<table class="table directory-table">
							<thead>
							<tr>
								<th>Full name</th>
								<th>Title</th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ( $data->{'Executive Directory'}[0][1] as $row ): ?>
								<tr>
									<td><?= $row[1] ?></td>
									<td><?= $row[2] ?></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</dd>
				<?php endif; ?>
			</dl>
		</div>
	</div>
</div>