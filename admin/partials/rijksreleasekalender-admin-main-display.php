<?php

/**
 * Provide a main admin area view for the plugin
 *
 * @link       http://nostromo.nl
 * @since      1.0.0
 *
 * @package    rijksreleasekalender
 * @subpackage rijksreleasekalender/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<h3><?php _e( 'Synchroniseren', 'rijksreleasekalender' ); ?></h3>

	<table class="form-table" id="progress">
		<tr>
			<td>
				<input id="startsync" type="button" class="button button-primary" value="<?php _e( 'Start synchronisatie', 'rijksreleasekalender' ); ?>" />
				<input id="clearlog" type="button" class="button button-secondary" value="<?php _e( 'Log leegmaken', 'rijksreleasekalender' ); ?>" />
			</td>
		</tr>
	</table>
	<noscript style="background: red; padding: .5em; font-size: 120%;display: block; margin-top: 1em !important; color: white;"><strong><?php _e( 'Dit werkt alleen als je JavaScript hebt aangezet.', 'rijksreleasekalender' );?></strong></noscript>
	<div style="width: 100%; padding-top: 16px; font-style: italic;" id="log"><?php _e( 'Druk op de knop!', 'rijksreleasekalender' );?></div>

		<?php //todo this needs to be moved to an external script. ?>

	<script type="text/javascript">


		var _button = jQuery('input#startsync');
		var _clearbutton = jQuery('input#clearlog');
		var _lastrow = jQuery('#progress tr:last');

		var setProgress = function (_message) {
			_lastrow.append(_message);
		}

		jQuery(document).ready(function () {

			_button.click(function (e) {

				e.preventDefault();

				jQuery(this).val('<?php _e( 'Sync gestart...', 'rijksreleasekalender' );?>').prop('disabled', true);
				jQuery( '#log' ).empty();
				_requestJob(0);

			});

			// clear log div
			_clearbutton.click(function() {
				jQuery( '#log' ).empty();
			})

		})

		var _requestJob = function (_start) {
			jQuery.post(ajaxurl, {'action': 'rrk_do_sync', 'step': _start}, _jobResult);
		}

		var _jobResult = function (response) {
			if (response.messages.length > 0) {
				for (var i = 0; i < response.messages.length; i++) {
					// new messages appear on top. .append() can be used to have new entries at the bottom
					jQuery('#log').prepend(response.messages[i] + '<br />');

				}
			}

      console.log('Het resultaat: ' + response.result);

      switch (response.result) {
        case 0:
          // De voorzieningen
        case 1:
          // De producten
        case 2:
          // De releases
        case 3:
          // De releaseafspraken
        case 4:
          // De releaseafhankelijkheden
        case 5:
          _requestJob(response.step);
          break;
        case 'done':
          _button.val('<?php _e( 'Start Synchronisatie', 'rijksreleasekalender' );?>').prop('disabled', false);
          break;
        default:
          _button.val('<?php _e( 'Sync fout', 'rijksreleasekalender' );?>');
          break;
      }
		}

	</script>
</div>