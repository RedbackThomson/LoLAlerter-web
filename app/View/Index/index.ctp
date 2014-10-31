<section id="about" class="loggedOut">
	<div class="row clearfix">
		<div class="col-md-4">
			<h2>About</h2>
			<p class="well">
				LoLAlerter is a tool which simplifies the process of getting information to the streamer.<br/>
				Even in the fastest Twitch chats, LoLAlerter provides the streamer with information about
				the people that support them.<br/>
				The bot sends a League of Legends message to the streamer containing their latest subscriber without
				any need for interaction.				
			</p>
		</div>
		<div class="col-md-8">
			<h2>Getting Started</h2>
			<p class="well">
				In order for the bot to access your subscribers, you must use a partnered Twitch account.<br/><br/>
				Once the account has been configured, you simply add the bot in LoL and it will run transparently.
			</p>

			<h2>Statistics</h2>
			<div class="well">
				<dl class="dl-horizontal" style="margin-bottom: 0px;">
					<dt>Total Subscribed<small><abbr title="Since April 17, 2014">(?)</abbr></small></dt>
					<dd><?php echo($totalSubscribed); ?></dd>
					<dt>Online Users</dt>
					<dd><?php echo($onlineUsers); ?></dd>
					<dt>Largest Donation</dt>
					<dd>$<?php echo($largestDonation); ?></dd>
				</dl>
			</div>

			<h4>Contact</h4>
			<p class="well">
				If there are any questions, complaints or recommendations, you can contact Redback at <a target="_self" href="mailto:redback93@hotmail.com">his email</a> or on <a target="_new" href="http://twitch.tv/Redback93">Twitch.tv</a>.
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
						<label for="settingsLoLRegion">League Region</label>
						<button type="button" class="btn btn-default dropdown-toggle form-control input-sm" id="settingsLoLRegion" data-toggle="dropdown">North America <span class="caret"></span></button>
						<ul class="dropdown-menu" style="position:relative;width:100%;">
							<?php foreach($regions as $region):?>
							<li><a class="newRegion" href="#" region-code="<?php echo($region['RegionCode']); ?>"><?php echo($region['RegionName']); ?></a></li>
							<?php endforeach;?>
						</ul>
					</div>
					<div class="form-group">
						<label for="subscriptionStatus">Subscription Status</label><br/>
						<h4><span class="label label-success" id="subscriptionStatus">Subscribed</span></h4>
					</div>
				</form>
			</div>
			<h2>Notifications</h2>
			<div class="well">
				<div class="form-group">
					<label for="settingsInGameMessage">In Game Message (<abbr title="Substitute the user's name with %s">?</abbr>)</label>
					<input type="text" class="form-control input-sm" id="settingsInGameMessage">
					<label for="settingsInChatMessage">In Chat Message (<abbr title="Substitute the user's name with %s">?</abbr>)</label>
					<input type="text" class="form-control input-sm" id="settingsInChatMessage">
					<input class="btn btn-success" style="margin-top: 5px;" type="button" value="Update" id="messagesUpdate"></input>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="headingOne">
						<h4 class="panel-title">
							<a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
								Set Up
							</a>
						</h4>
					</div>
					<div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
						<div class="panel-body">
							<dl class="dl-horizontal" style="margin-bottom: 0px;">
								<dt>Step 1</dt>
								<dd>Add summoners using the box below</dd>
								<dt>Step 2</dt>
								<dd>On each of the accounts, add the bot (LoLAlerter) to your friends list</dd>
								<dt>Step 3</dt>
								<dd>Start Streaming!</dd>
							</dl>
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="headingTwo">
						<h4 class="panel-title">
							<a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								Twitch Bot
							</a>
						</h4>
					</div>
					<div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
						<div class="panel-body">
							<p>Commands for the Twitch chat bot</p>
							<p>You must moderate 'Redbackbot' to enable it</p>
							<dl class="dl-horizontal" style="margin-bottom: 0px">
								<dt>!summoner</dt>
								<dd>Outputs the current summoner account</dt>
								<dt>!runes</dt>
								<dd>Outputs a summary of the current rune page</dt>
								<dt>!champion</dt>
								<dd>Outputs the current champion (if in-game)</dt>
								<dt>!league / !rank</dt>
								<dd>Outputs the ranked stats for the current summoner</dt>
								<dt>!last</dt>
								<dd>Outputs the last subscriber</dt>
								<dt>!lolking</dt>
								<dd>Outputs the LoLKing profile of the current summoner</dt>
								<dt>!lolnexus</dt>
								<dd>Outputs the LoLNexus link for the current game</dt>
							</dl>
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="headingThree">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="true" aria-controls="collapseThree">
								Subscriber Statistics
							</a>
						</h4>
					</div>
					<div id="collapseThree" class="panel-collapse in" role="tabpanel" aria-labelledby="headingThree">
						<div class="panel-body">
							Coming Soon!
						</div>
					</div>
				</div>
			</div>
			<h2>Summoners</h2>
			<div id="newSummoner"><div style="width: 25%;float: left;margin-right: 5px;"><input type="text" class="form-control" placeholder="Summoner Name" /></div><input type="button" class="btn btn-success" value="Add" /></div>
			<div class="well" id="summoners">
				<div class="loading-image"></div>
			</div>
			<script id="summonerTemplate" type="text/x-jQuery-tmpl">
				<div class="summoner">
					<button class="close" type="button" onclick="LoLAlert.removeSummoner('${SummonerID}')"><span aria-hidden="true">×</span><span class="sr-only">Delete</span></button>
					<img class="summonerIcon" src="http://avatar.leagueoflegends.com/${Region}/${TrimName(SummonerName)}.png"/>
					<h2 class="summonerName">${SummonerName}</h2><br/>
					<span class="summonerDivision">Level ${Level} ${Division}</span>
				</div>
			</script>
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
					Response to the support will be through your Twitter. If you wish for any other method, please specify it in the body.
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
<div class="modal fade subscribe-modal" tabindex="-1" role="dialog" aria-labelledby="Subscribe" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Subscribe</h4>
			</div>
			<div class="modal-body">
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>