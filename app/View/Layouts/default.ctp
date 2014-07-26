<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<!DOCTYPE html>
<html>
<head>
	<?php $start_time = microtime(true); ?>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo 'LoL Alerter' ?> - 
		<?php echo $title_for_layout; ?>
	</title>
	<?php 
		echo $this->Html->meta('icon', '/favicon.png'); 
		echo $this->Html->css('bootstrap.min');
	?>
	<meta name="Description" content="A League of Legends in game chat bot which will alert the Twitch.tv user when a new user subscribes. The bot runs without any interaction once it is set up." />
	<style type="text/css">
body {
	padding-top: 60px;
}
section {
	padding-top: 5px;
	margin-bottom: -5px;
}
footer {
	font-size: 12px;
}
.loggedIn {
	display: none;
}
	</style>
</head>
<body>
	<div class="container">
		<div class="row clearfix">
			<div class="col-md-12 column">
				<nav class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
					<div class="container">
						<div class="navbar-header">
							 <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"> <span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button> <a class="navbar-brand" href="/"><img style="margin-top: -5px;" src="/img/keyboard_magnify.png">&nbsp;LoL Alerter</a>
						</div>
						
						<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
							<ul class="nav navbar-nav loggedIn">
								<li>
									<a href="#" class="twitch-disconnect">Logout</a>
								</li>
								<li>
									<a href="#" data-toggle="modal" data-target=".support-modal">Support</a>
								</li>
							</ul>
							<p class="navbar-text navbar-right loggedIn" id="navUsername"></p>
							<ul class="nav navbar-nav navbar-right loggedOut" style="margin-top: 17px; display: list-item;">
								<li>
									<img href="#" style="cursor: pointer;" class="twitch-connect" src="http://ttv-api.s3.amazonaws.com/assets/connect_dark.png" />
								</li>
							</ul>
						</div>
					</div>
				</nav>
			</div>
		</div>
	</div>
	<!-- Here's where the fun starts -->
	<div class="container">
		<section>
			<div class="row">
				<div id="alerts" class="span12">
				</div>
			</div>
		</section>
		<?php echo $this->Session->flash(); ?>
		<?php echo $this->fetch('content'); ?>
	</div>
	<div class="container">
		<hr/>
		<footer>
			Page generated in <?php echo(number_format(microtime(true) - $start_time, 3));?> seconds &copy; Created by <a href="http://softcode.co/">Redback93</a> • <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=lolalerter%40gmail%2ecom&lc=GB&item_name=LoLAlerter Donation&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted">Donate!</a>
		</footer>
	</div>
	<?php echo $this->element('sql_dump'); ?>
</body>
<!-- Putting the scripts at the end, because that's pro -->
<?php
	echo $this->Html->script('jquery.min');
	echo $this->Html->script('bootstrap.min');
	echo $this->Html->script('https://ttv-api.s3.amazonaws.com/twitch.min.js');
	echo $this->Html->script('jquery.dataTables');
	echo $this->Html->script('dataTables.bootstrap.js');
	echo $this->Html->script('paging');


	echo $this->fetch('css');
	echo $this->fetch('script');
?>
<script>
$(document).ready(function() {
	LoLAlert = {
		alert: function(kind, title, message) {
			$("#alerts").append('<div class="alert alert-dismissable alert-'+kind+'"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>'+title+'</strong>&nbsp;'+message+'</div>')
		},
		newLoLUsername: function(lolusername, callback){
			$.getJSON('/api/summoners/'+LoLAlert.userData.apikey+'/'+lolusername, function(data) {
				if(data.success == true) { 
					$("#settingsLoLUsername").val(lolusername);
					LoLAlert.alert('success', 'Updated!', 'Settings have been successfully updated.');
				}
				else
				{
					if(data.error)
						LoLAlert.alert('danger', 'Error', data.error);
					else
						LoLAlert.alert('danger', 'Error', 'Unknown error');
				}
				if(callback) {
					callback();
				}
			});
		},
		newUserData: function(username, token, callback){
			$.getJSON('/api/user/'+username+'/'+token, function(data) {
				if(data.success == true) { 
					LoLAlert.userData.apikey = data.APIKey;
					$("#settingsTwitchUsername").val(data.TwitchUsername);
					$("#settingsLoLUsername").val(LoLAlert.getSummonersList(data.summoners));

					$.getJSON('/api/subscribers/'+data.TwitchUsername+'/'+LoLAlert.userData.apikey + '/1', function(tabledata) {
						if(tabledata.success == true)
						{
							$(".dataTable").dataTable( {
								"bProcessing": true,
								"sAjaxSource": '/api/subscribers/'+data.TwitchUsername+'/'+LoLAlert.userData.apikey,
								"sDom": "<'row'<'col-md-6 pull-left'f><'col-md-6 pull-right'l>r>t<'row'<'col-md-12 pull-left'i><'col-md-12 center'p>>",
								"sPaginationType": "bootstrap"
							});
						}
						else
						{
							LoLAlert.alert('danger', 'Error', data.error);
						}
					});
				}
				if(callback) {
					callback();
				}
			});
		},
		updateTwitchUsername: function(username){
			$("#navUsername").text('Logged in as '+username);
		},
		getSummonersList: function(summoners)
		{
			var retVal = "";
			jQuery.each(summoners, function() {
			  	retVal += this.SummonerName + ",";
			});
			retVal = retVal.substring(0, retVal.length - 1);
			return retVal;
		},
	};
	function ChangePanels(login)
	{
		if(login)
		{
			$('.loggedOut').hide();
			$('.loggedIn').show();
		}
		else
		{
			$('.loggedOut').show();
			$('.loggedIn').hide();
		}
	}
	$('#supportForm').submit(function () 
	{
	    var name = $.trim($('#supportForm #inputUsername').val());
	    var body = $.trim($('#supportForm #inputBody').val());
	    return !(name == '' || body == '');
	});
	if(window.location.hash) {
  		var hash = window.location.hash.substring(1);
  		if(hash == 'support') LoLAlert.alert('success', 'Sent!', 'Your support message was sent. You should expect a reply to your Twitch.tv inbox within 24 hours.');
	}
	Twitch.init({clientId: '<?php echo Configure::read('LoLAlert.ClientID'); ?>'}, function(error, status) {
		$('.twitch-connect').click(function() {
		  Twitch.login({
		  	redirect_uri: "<?php echo Configure::read('LoLAlert.Redirect'); ?>",
		    scope: ['user_read', 'channel_subscriptions']
		  });
		});
		$('.twitch-disconnect').click(function(event) {
			Twitch.logout(function(error) {
				ChangePanels(false);
			}); 
		});
		$("#updateData").submit(function(event) {
			var newLoLUsername = $("#settingsLoLUsername").val();
			LoLAlert.newLoLUsername(newLoLUsername);
			event.preventDefault();
		});

		if (status.authenticated) {
			Twitch.api({method: 'user'}, function(error, user) {
				$.getJSON('/api/partner/'+user.display_name, function(data) {
					if(data.partner)
					{
						//Methods to be run after login
						LoLAlert.updateTwitchUsername(user.display_name);

						user.token = Twitch.getToken();
						LoLAlert.userData = user;
						LoLAlert.newUserData(user.display_name, status.token);

						ChangePanels(true);
					}
					else
						LoLAlert.alert('danger', 'Error', 'You must be a Twitch.tv partner to use this feature')
				});
			});
		}
	});
});
</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-50560855-1', 'softcode.co');
  ga('send', 'pageview');

</script>
</html>
