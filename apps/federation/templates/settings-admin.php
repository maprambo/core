<?php
/** @var array $_ */
/** @var OC_L10N $l */
script('federation', 'settings-admin');
style('federation', 'settings-admin')
?>
<div id="ocFederationSettings" class="section">
	<h2><?php p($l->t('Federation')); ?></h2>
	<em><?php p($l->t('ownCloud Federation allows you to connect with other trusted ownClouds to exchange the user directory. For example this will be used to auto-complete external users for federated sharing.')); ?></em>

	<p id="ocFederationShareUsers">
		<input type="checkbox" class="checkbox" id="shareUsers" />
		<label for="shareUsers">Share internal user list with other ownClouds</label>
	</p>

	<p id="ocFederationAddServer">
		<label for="serverUrl">Add ownCloud Server:</label>
		<input id="serverUrl" type="text" value="" placeholder="ownCloud Server" name="server_url"/>
		<span class="msg"></span>
	</p>

	<h3>Trusted ownCloud Servers</h3>
	<ul id="listOfTrustedServers">
		<?php foreach($_['trustedServers'] as $trustedServer) { ?>
			<li url="<?php p($trustedServer['url']); ?>">
				<?php p($trustedServer['url']); ?>
			</li>
		<?php } ?>
	</ul>

</div>

