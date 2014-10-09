<h4>Current Status</h4>
<h4><?php if($active): ?>
	<span class="label label-success">Subscribed</span>
<?php else: ?>
	<span class="label label-danger">Not Subscribed</span>
<?php endif;?></h4>
<h4>Payment History</h4>
<table class="table table-striped">
	<tr><th>Transaction #</th><th>Amount</th><th>Payment Date</th></tr>
<?php foreach($payments as $payment): ?>
	<tr><td><?php echo($payment['Transaction']); ?></td><td>USD$<?php echo($payment['Amount']); ?></td><td><?php echo($payment['Timestamp']); ?> PST</td></tr>
<?php endforeach;?>
</table>
<?php if($active): ?>
<form action="https://www.paypal.com/cgi-bin/customerprofileweb" method="post" target="_top">
	<input type="hidden" name="cmd" value="_manage-paylist">

	<button class="btn btn-danger" type="submit">Cancel</button>
</form>
<?php else: ?>
<button class="btn btn-success" id="subscribeStart">Subscribe</button>
<script>
$("#subscribeStart").click(function () {
	$("#subscribeStart").hide();
	$("#subscribeTerms").show();
});
</script>

<div id="subscribeTerms" style="display: none;">
	<h4>Terms</h4>
	<p>Due to a lack of donations, LoLAlerter will no longer be free after November 1, 2014. 
		If there are insufficient subscriptions the program's continued support
		will be assessed.</p>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_xclick-subscriptions">
		<input type="hidden" name="no_note" value="1">
		<input type="hidden" name="no_shipping" value="1">
		<input type="hidden" name="rm" value="1">
		<input type="hidden" name="src" value="1">
		<input type="hidden" name="a3" value="<?php echo($monthly); ?>">
		<input type="hidden" name="p3" value="1">
		<input type="hidden" name="t3" value="M">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="business" value="<?php echo($email); ?>">
		<input type="hidden" name="item_name" value="LoLAlerter Subscription (<?php echo($display); ?>)">
		<input type="hidden" name="item_number" value="<?php echo($item); ?>">
		<input type="hidden" name="return" value="http://lolalerter.com/">
		<input type="hidden" name="cancel_return" value="http://lolalerter.com/">
		<input type="hidden" name="notify_url" value="http://lolalerter.com/Ipn/">

		<button class="btn btn-success" type="submit">Subscribe</button>
	</form>
	<i class="glyphicon glyphicon-info-sign"></i> Payment may take a while to come through
</div>
<?php endif;?>