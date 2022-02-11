<div class="row">
	<div class="col-md-<?php echo (!isset($_GET['page']) || (isset($_GET['page']) && $_GET['page'] == 'server')) ? '12' : '10'; ?>">

		<?php if(isset($_GET['page']) && ($_GET['page'] !== 'my_favorites' && $_GET['page'] !== 'my_servers' && $_GET['page'] !== 'servers' && $_GET['page'] !== 'profile' && $_GET['page'] !== 'server'	&& $_GET['page'] !== 'category' && $_GET['page'] !== 'admin_categories_management')) { ?>
		<div class="panel panel-default">
				<div class="panel-body">
		<?php } ?>