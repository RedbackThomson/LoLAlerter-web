<section id="admin">
	<div class="row clearfix">
		<div class="col-md-12 column">
			<h2>Admin</h2>
			<div class="well">
				<p>
					<h5>Server Status: </h5><?php echo(($serverStatus?"Online":"Offline")); ?>
				</p>
				<p>
					<a href="/Admin/<?php echo $password; ?>/Start"><input type="button" value="Start" class="btn btn-success btn-sm" /></a>
					<a href="/Admin/<?php echo $password; ?>/Stop"><input type="button" value="Stop" class="btn btn-danger btn-sm" /></a>
				</p>
			</div>
		</div>
	</div>
</section>