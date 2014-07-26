<section id="about" class="loggedOut">
	<div class="row clearfix">
		<div class="col-md-4">
			<h2>About It</h2>
			<p class="well">
				For large streamers, it can sometimes be difficult to track the people that are subscribing to your channel - either because the chat moves too quickly or you are in the middle of a game. However, the LoL chat enables an easy way to get the message through to the streamer while they're playing the game. LoL Alerter does exactly this - it will send you a message when you get a new subscriber.
			</p>
		</div>
		<div class="col-md-8">
			<h2>Getting Started</h2>
			<p class="well">
				To be able to send messages, the LoL Alerter bot will need to be in your friends list. This bot is not run by a person (the account will never be used to play LoL), and such your privacy is safe.<br/><br/>
				For the bot to be able to access your subscriber list, it needs a verification from Twitch. This is why it is necessary that you log in with Twitch.tv.<br/><br/>
				If there are any questions, complaints or recommendations, you can contact the developer at <a target="_self" href="mailto:redback93@hotmail.com">my email</a> or on <a target="_new" href="http://twitch.tv/Redback93">Twitch.tv</a>.
			</p>
		</div>
	</div>
</section>
<section id="settings" class="loggedIn">
	<div class="row clearfix">
		<div class="col-md-4">
			<h2>Settings</h2>
			<div class="well">
				<form role="form" id="updateData">
					<div class="form-group">
						<label for="settingsTwitchUsername">Twitch Account</label>
						<input type="text" class="form-control input-sm" id="settingsTwitchUsername" readonly="readonly">
					</div>
					<div class="form-group">
						<label for="settingsLoLUsername">LoL Summoner Names</label>
						<textarea class="form-control input-sm" id="settingsLoLUsername" placeholder="Summoner1,Summoner2" value=""></textarea>
					</div>
					<button type="submit" class="btn btn-primary btn-sm">Update</button>
				</form>
			</div>
		</div>
		<div class="col-md-8">
			<h2>Set Up</h2>
			<p class="well">
				It's very easy to set up LoLAlerter - two easy steps:<br/><br/>
				1. Add each of your summoner names to the "LoL Summoner Names" box on the left (separated by commas).<br/>
				2. On each of the accounts, add the bot to your friends list.<br/><br/>
				The username of the bot is "LoLAlerter" - on the NA server.<br/>
				If the bot does not accept your friend request, check that the summoner has been added to the list.
			</p>
			<h2>Subscribers</h2>

			<div role="grid" class="dataTables_wrapper form-inline" id="donations-table_wrapper">
				<table style="width: 769px;" id="donations-table" class="table table-bordered table-striped bootstrap-datatable datatable dataTable" aria-describedby="donations-table_info">
					<thead>
						<tr role="row">
							<th style="width:170px;" class="sorting" role="columnheader">Twitch Username</th>
							<th class="sorting-desc" role="columnheader" tabindex="0">Subscription Date</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
</section>
<div class="modal fade support-modal" tabindex="-1" role="dialog" aria-labelledby="Support" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Support</h4>
			</div>
			<div class="modal-body">
				<p>
					If you're having any issues with the bot or your configuration, please specify the specific summoners that are being affected.
				</p>
				<p>
					Response to the support will be through your Twitch.tv inbox. If you wish for any other method, please specify it in the body.
				</p>
				<form id="supportForm" class="form-horizontal" role="form" action="/issue" method="POST">
					<div class="form-group">
						<div class="col-sm-12">
							<input type="email" class="form-control" id="inputUsername" name="username" placeholder="Username" required>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<textarea class="form-control" id="inputBody" placeholder="Your Issue" name="body" rows="3" required></textarea>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onClick="$('#supportForm').submit();">Send!</button>
			</div>
		</div>
	</div>
</div>