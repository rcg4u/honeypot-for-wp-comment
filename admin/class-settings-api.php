<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
/**
 * Settings API wrapper class
 *
 * @since      2.0.0
 *
 * @package    Honeypot_Wp_Comment
 * @subpackage Honeypot_Wp_Comment/admin
 * @author     Prasidhda <pranuk.prwnsi@gmail.com>
 */
if ( ! class_exists( 'Honeypot_Settings_API' ) ):
	class Honeypot_Settings_API {

		/**
		 * settings sections array
		 *
		 * @var array
		 */
		protected $settings_sections = array();

		/**
		 * Settings fields array
		 *
		 * @var array
		 *
		 * @since  2.0.0
		 */
		protected $settings_fields = array();

		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		/**
		 * Enqueue scripts and styles
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function admin_enqueue_scripts() {
			wp_enqueue_script( 'jquery' );
		}

		/**
		 * Set settings sections
		 *
		 * @param array $sections setting sections array
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function set_sections( $sections ) {
			$this->settings_sections = $sections;

			return $this;
		}

		/**
		 * Add a single section
		 *
		 * @param array $section
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function add_section( $section ) {
			$this->settings_sections[] = $section;

			return $this;
		}

		/**
		 * Set settings fields
		 *
		 * @param array $fields settings fields array
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function set_fields( $fields ) {
			$this->settings_fields = $fields;

			return $this;
		}

		/**
		 * @param $section
		 * @param $field
		 *
		 * @return $this
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function add_field( $section, $field ) {
			$defaults = array(
				'name'  => '',
				'label' => '',
				'desc'  => '',
				'type'  => 'text'
			);

			$arg                                 = wp_parse_args( $field, $defaults );
			$this->settings_fields[ $section ][] = $arg;

			return $this;
		}

		/**
		 * Initialize and registers the settings sections and fileds to WordPress
		 *
		 * Usually this should be called at `admin_init` hook.
		 *
		 * This function gets the initiated settings sections and fields. Then
		 * registers them to WordPress and ready for use.
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function admin_init() {
			//register settings sections
			foreach ( $this->settings_sections as $section ) {
				if ( false == get_option( $section['id'] ) ) {
					add_option( $section['id'] );
				}

				if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
					$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
					$callback        = create_function( '', 'echo "' . str_replace( '"', '\"', $section['desc'] ) . '";' );
				} else if ( isset( $section['callback'] ) ) {
					$callback = $section['callback'];
				} else {
					$callback = null;
				}

				add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
			}

			//register settings fields
			foreach ( $this->settings_fields as $section => $field ) {
				foreach ( $field as $option ) {

					$type = isset( $option['type'] ) ? $option['type'] : 'text';

					$args = array(
						'id'                => $option['name'],
						'label_for'         => $args['label_for'] = "{$section}[{$option['name']}]",
						'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
						'name'              => $option['label'],
						'section'           => $section,
						'size'              => isset( $option['size'] ) ? $option['size'] : null,
						'options'           => isset( $option['options'] ) ? $option['options'] : '',
						'std'               => isset( $option['default'] ) ? $option['default'] : '',
						'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
						'type'              => $type,
					);

					add_settings_field( $section . '[' . $option['name'] . ']', $option['label'], array(
						$this,
						'callback_' . $type
					), $section, $section, $args );
				}
			}

			// creates our settings in the options table
			foreach ( $this->settings_sections as $section ) {
				register_setting( $section['id'], $section['id'], array( $this, 'sanitize_options' ) );
			}
		}

		/**
		 * Get field description for display
		 *
		 * @param array $args settings field args
		 */
		public function get_field_description( $args ) {
			if ( ! empty( $args['desc'] ) ) {
				$desc = sprintf( '<p class="description">%s</p>', $args['desc'] );
			} else {
				$desc = '';
			}

			return $desc;
		}

		/**
		 * @param $args
		 *
		 * @return string|void
		 *
		 * @since  2.0.0
		 * @access public
		 */
		public function get_value( $args ) {
			return esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		}

		/**
		 * Displays a text field for a settings field
		 *
		 * @param array $args settings field args
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function callback_text( $args ) {

			$value = $this->get_value( $args );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type  = isset( $args['type'] ) ? $args['type'] : 'text';

			$html = sprintf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"/>', $type, $size, $args['section'], $args['id'], $value );
			$html .= $this->get_field_description( $args );

			echo $html;
		}

		/**
		 * Displays a checkbox for a settings field
		 *
		 * @param array $args settings field args
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function callback_checkbox( $args ) {

			$value = $this->get_value( $args );

			$html = '<fieldset>';
			$html .= sprintf( '<label for="wpuf-%1$s[%2$s]">', $args['section'], $args['id'] );
			$html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id'] );
			$html .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked( $value, 'on', false ) );
			$html .= sprintf( '%1$s</label>', $args['desc'] );
			$html .= '</fieldset>';

			echo $html;
		}

		/**
		 * Displays a selectbox for a settings field
		 *
		 * @param array $args settings field args
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function callback_select( $args ) {

			$value = $this->get_value( $args );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$html  = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );

			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
			}

			$html .= sprintf( '</select>' );
			$html .= $this->get_field_description( $args );

			echo $html;
		}

		/**
		 * Displays a textarea for a settings field
		 *
		 * @param array $args settings field args
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function callback_textarea( $args ) {

			$value = $this->get_value( $args );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

			$html = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]">%4$s</textarea>', $size, $args['section'], $args['id'], $value );
			$html .= $this->get_field_description( $args );

			echo $html;
		}

		/**
		 * Sanitize callback for Settings API
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function sanitize_options( $options ) {
			foreach ( $options as $option_slug => $option_value ) {
				$sanitize_callback = $this->get_sanitize_callback( $option_slug );

				// If callback is set, call it
				if ( $sanitize_callback ) {
					$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
					continue;
				}
			}

			return $options;
		}

		/**
		 * Get sanitization callback for given option slug
		 *
		 * @param string $slug option slug
		 *
		 * @return mixed string or bool false
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function get_sanitize_callback( $slug = '' ) {
			if ( empty( $slug ) ) {
				return false;
			}

			// Iterate over registered fields and see if we can find proper callback
			foreach ( $this->settings_fields as $section => $options ) {
				foreach ( $options as $option ) {
					if ( $option['name'] != $slug ) {
						continue;
					}

					// Return the callback name
					return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
				}
			}

			return false;
		}

		/**
		 * Get the value of a settings field
		 *
		 * @param string $option settings field name
		 * @param string $section the section name this field belongs to
		 * @param string $default default text if it's not found
		 *
		 * @return string
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function get_option( $option, $section, $default = '' ) {

			$options = get_option( $section );

			if ( isset( $options[ $option ] ) ) {
				return $options[ $option ];
			}

			return $default;
		}

		/**
		 * Show navigations as tab
		 *
		 * Shows all the settings section labels as tab
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function show_navigation() {
			$html = '<h2 class="nav-tab-wrapper">';

			foreach ( $this->settings_sections as $tab ) {
				$html .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title'] );
			}

			$html .= '</h2>';

			echo $html;
		}

		/**
		 * Show the section settings forms
		 *
		 * This function displays every sections in a different form
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function show_forms() {
			?>
            <div class="metabox-holder">
				<?php foreach ( $this->settings_sections as $form ) { ?>
                    <div id="<?php echo $form['id']; ?>" class="group" style="display: none;">
                        <form method="post" action="options.php">
							<?php
							settings_fields( $form['id'] );
							do_settings_sections( $form['id'] );
							?>
                            <div style="padding-left: 10px">
								<?php submit_button(); ?>
                            </div>
                        </form>
                    </div>
				<?php } ?>
            </div>
			<?php
			$this->script();
		}

		/**
		 * This code uses localstorage for displaying active tabs
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function script() {
			?>
            <script>
                jQuery(document).ready(function ($) {

                    // id conflicts fix for _wpnonce
                    $.each($('input[name="_wpnonce"]'), function (index, value) {
                        $(value).attr('id', 'nonce_' + $(value).parents('.group').attr('id'))
                    });
                    // id conflicts for submit
                    $.each($('input[name="submit"]'), function (index, value) {
                        $(value).attr('id', 'submit_' + $(value).parents('.group').attr('id'))
                    });

                    // Switches option sections
                    $('.group').hide();
                    let activeTab = '';
                    if (typeof (localStorage) != 'undefined') {
                        activeTab = localStorage.getItem("active_tab");
                    }
                    if (activeTab !== '' && $(activeTab).length) {
                        $(activeTab).fadeIn();
                    } else {
                        $('.group:first').fadeIn();
                    }
                    $('.group .collapsed').each(function () {
                        $(this).find('input:checked').parent().parent().parent().nextAll().each(
                            function () {
                                if ($(this).hasClass('last')) {
                                    $(this).removeClass('hidden');
                                    return false;
                                }
                                $(this).filter('.hidden').removeClass('hidden');
                            });
                    });

                    if (activeTab !== '' && $(activeTab + '-tab').length) {
                        $(activeTab + '-tab').addClass('nav-tab-active');
                    } else {
                        $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
                    }
                    $('.nav-tab-wrapper a').click(function (evt) {
                        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                        $(this).addClass('nav-tab-active').blur();
                        var clicked_group = $(this).attr('href');
                        if (typeof (localStorage) != 'undefined') {
                            localStorage.setItem("active_tab", $(this).attr('href'));
                        }
                        $('.group').hide();
                        $(clicked_group).fadeIn();
                        evt.preventDefault();
                    });

                });
            </script>

            <style type="text/css">
                /** WordPress 3.8 Fix **/
                .form-table th {
                    padding: 20px 10px;
                }

                #wpbody-content .metabox-holder {
                    padding-top: 5px;
                }
            </style>
			<?php
		}

	}
endif;
