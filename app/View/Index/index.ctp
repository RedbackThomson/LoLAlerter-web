<section id="about" class="loggedOut">
	<div class="row clearfix">
		<div class="col-md-4 column">
			<h2>About It</h2>
			<p class="well">
				For large streamers, it can sometimes be difficult to track the people that are subscribing to your channel - either because the chat moves too quickly or you are in the middle of a game. However, the LoL chat enables an easy way to get the message through to the streamer while they're playing the game. LoL Alerter does exactly this - it will send you a message when you get a new subscriber.
			</p>
		</div>
		<div class="col-md-8 column">
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
		<div class="col-md-4 column">
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
		<div class="col-md-8 column">
			<h2>Set Up</h2>
			<p class="well">
				This is where you get told where to set shit up
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