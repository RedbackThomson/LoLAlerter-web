<link href='http://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
<table class="table table-hover" style="font-family: 'Lato', sans-serif;">
	<tr>
		<th></th>
		<th colspan="2">New Subs</th>
		<th colspan="2">UnSubs</th>
	</tr>
	<?php foreach($stats as $key => $value):?>
	<tr>
		<td><?php echo($key);?></td>
		<td><?php echo($value['now']) ?></td>
		<td>
		<?php if($value['two'] > $value['now']): ?>
			<span title="<?php echo($value['desc']); ?>" class="label label-danger"><span class="glyphicon glyphicon-arrow-down"></span><?php echo($value['two']-$value['now']);?></span>
		<?php else: ?>
			<span title="<?php echo($value['desc']); ?>" class="label label-success"><span class="glyphicon glyphicon-arrow-up"></span><?php echo($value['now']-$value['two']);?></span>
		<?php endif; ?>
		</td>
		<td><?php echo($value['unnow']) ?></td>
		<td>
		<?php if($value['untwo'] > $value['unnow']): ?>
			<span title="<?php echo($value['desc']); ?>" class="label label-danger"><span class="glyphicon glyphicon-arrow-down"></span><?php echo($value['untwo']-$value['unnow']);?></span>
		<?php else: ?>
			<span title="<?php echo($value['desc']); ?>" class="label label-success"><span class="glyphicon glyphicon-arrow-up"></span><?php echo($value['unnow']-$value['untwo']);?></span>
		<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?> 
</table>