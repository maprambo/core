/**
 * @author Björn Schießle <schiessle@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

$(document).ready(function () {

	// add new trusted server
	$("#serverUrl").keyup(function (e) {
		if (e.keyCode === 13) {
			var url = $('#serverUrl').val();
			OC.msg.startSaving('#ocFederationAddServer .msg');
			$.post(
				OC.generateUrl('/apps/federation/ajax/addServer'),
				{
					url: url
				}
			).done(function (data) {
					$('#serverUrl').attr('value', '');
					$('ul#listOfTrustedServers').append(
						$('<li>').attr('url', data.url).text(data.url)
					);
					OC.msg.finishedSuccess('#ocFederationAddServer .msg', data.message);
				})
				.fail(function (jqXHR) {
					OC.msg.finishedError('#ocFederationAddServer .msg', JSON.parse(jqXHR.responseText).message);
				});
		}
	});

	// remove trusted server from list
	$( "#listOfTrustedServers" ).on('click', 'li', function() {
		var url = $(this).attr('url');
		var $this = $(this);
		$.ajax({
			url: OC.generateUrl('/apps/federation/ajax/removeServer'),
			type: 'DELETE',
			data: {	url: url },
			success: function(response) {
				$this.remove();
			}
		});

	});

});
