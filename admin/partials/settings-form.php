<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
/**
 * Template for settings form
 *
 * @since      2.0.0
 *
 * @package    Honeypot_Wp_Comment
 * @subpackage Honeypot_Wp_Comment/admin/partials
 * @author     Prasidhda <pranuk.prwnsi@gmail.com>
 */
?>

<style>
    .wrap.honeypot-setting-wrap .group {
        max-width: 990px;
    }
</style>
<div class="wrap honeypot-setting-wrap">
	<?php $this->settings_api->show_navigation(); ?>
	<?php $this->settings_api->show_forms(); ?>
</div>