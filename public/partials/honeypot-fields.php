<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
/**
 * Honeypot input fields
 *
 * @since    2.0.0
 *
 * @package    Honeypot_Wp_Comment
 * @subpackage Honeypot_Wp_Comment/public/partials
 * @author     Prasidhda <pranuk.prwnsi@gmail.com>
 */

?>
<?php foreach ( $this->honeypot_fields as $field_name => $field_placeholder ): ?>
    <label class="hpwc" for="<?php echo $field_name; ?>"></label>
    <input class="hpwc" autocomplete="off"
           type="<?php echo strpos( strtolower( $field_name ), 'mail' ) !== false || strpos( strtolower( $field_placeholder ), 'mail' ) !== false ? 'email' : 'text'; ?>"
           id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>"
           placeholder="<?php echo $field_placeholder; ?>">
<?php endforeach; ?>