<table summary="pendingCaptainRequests" title="Pending Captain Requests">
	<thead>
		<tr>
			<th colspan="99" class="header"><h3>Pending Captain Requests</h3></th>
		</tr>
		<tr>
			<th>User Name</th>
			<th>Actual Name</th>
			<th>Team Name</th>
			<th>League Name</th>
			<th>Accept</th>
			<th>Decline</th>
		</tr>
	</thead>
	<?php foreach ($this->data as $row) : ?>
	<tr id="TableRowID">
		<td>coreysnyder04</td>
		<td>Corey Snyder</td>
		<td>The Hive</td>
		<td>D-West</td>
		<td><a data-action="accept" data-row="tableRowID" href="#">Accept</a></td>
		<td><a data-action="accept" data-row="tableRowID" href="#">Decline</a></td>
	</tr>
	<?php endforeach; ?>
</table>