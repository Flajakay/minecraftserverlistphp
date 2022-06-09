<?php
User::check_permission(1);

initiate_html_columns();

?>

<div class="table-responsive">
	<table class="table">
		<thead>
			<tr>
				<th><? echo $language['forms']['server_status'] ?></th>
				<th><? echo $language['forms']['server_address'] ?></th>
				<th><? echo $language['forms']['server_connection_port'] ?></th>
				<th><? echo $language['forms']['server_category'] ?></th>
				<th><? echo $language['forms']['server_date_added'] ?></th>
				<th><? echo $language['forms']['server_edit'] ?></th>
			</tr>
		</thead>
		<tbody id="results">
			
		</tbody>
	</table>
</div>

<script>
$(document).ready(function() {
	/* Load first answers */
	showMore(0, 'processing/admin_servers_show_more.php', '#results', '#showMoreServers');
});
</script>