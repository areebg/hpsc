<?php

/**
 *
 * @package     XStore Core plugin
 * @author      8theme
 * @version     1.0.0
 * @since       3.2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'EthemeAdmin' ) ) {
    return;
}

if ( ! method_exists( 'EthemeAdmin', 'get_instance' ) ) {
    return;
}

// Don't duplicate me!
if ( ! class_exists( 'Etheme_Slides' ) ) {
	
	
	/**
	 * Main Etheme_Slides class
	 *
	 * @since       3.2.2
	 */
	class Etheme_Slides {

        public static $key = 'etheme_slide';

        public $global_admin_class;

		/**
		 * Class Constructor. Defines the args for the actions class
		 *
		 * @return      void
		 * @version     1.0.1
		 * @since       3.2.2
		 * @access      public
		 */
		public function __construct() {
            $this->global_admin_class = EthemeAdmin::get_instance();
			
			add_action( 'wp_ajax_'.self::$key.'_settings', array(
				$this,
				'slide_settings'
			) );

            add_action( 'wp_ajax_'.self::$key.'_save_settings', array(
                $this,
                'slide_save_settings'
            ) );

		}

        public function slide_settings() {
            check_ajax_referer( 'etheme_'.$_POST['postType'].'_nonce', 'security' );
            $post = get_post($_POST['postId']);
            $global_admin_class = $this->global_admin_class;

            $response = array();
            ob_start();
                $response['content'] = $this->render_feature_settings_form(
                        $global_admin_class,
                        $post->post_slug,
                        $post);
            wp_send_json($response);
        }

        public function slide_save_settings() {
//            check_ajax_referer( 'etheme_'.$_POST['postType'].'_save_settings_nonce', 'security' );
            $post_id = $_POST['postId'];
            $post_type = 'etheme_slides';
            // get_post_meta( $slide_id, 'bg_image_size_desktop', true )
            foreach ($_POST['local_settings'] as $property => $value) {
                update_post_meta( $post_id, $post_type . '_' . str_replace('-', '_', $property) , $value );
            }
        }

        public function render_feature_settings_form($admin_class, $tab_content, $post) {
            $global_admin_class = $admin_class;
            $post_type = 'etheme_slides';
            $slide_id = $post->ID;
            $all_possible_properties = array(
                'background-color',
                'background-repeat',
                'background-position',
                'background-size'
            );
            $default_settings = array();
            foreach ($all_possible_properties as $possible_property) {
                $possible_property_key = str_replace('-', '_', $possible_property);
                $possible_property_value = get_post_meta($slide_id, $post_type . '_' . $possible_property_key, true);
                if (!$possible_property_value) {
                    switch ($possible_property) {
                        case 'background-color':
                            $possible_property_value = '';
                            break;
                        case 'background-repeat':
                            $possible_property_value = 'no-repeat';
                            break;
                        case  'background-position':
                            $possible_property_value = 'center center';
                            break;
                        case 'background-size':
                            $possible_property_value = 'cover';
                            break;
                    }
                }
                $default_settings[$possible_property_key] = $possible_property_value;
            }
            write_log($default_settings);
            ob_start(); ?>
            <h3>
                <?php echo sprintf(esc_html__('%s background settings', 'xstore-core'), esc_html($post->post_title)); ?>
            </h3>
            <p class="elementor-panel-alert elementor-panel-alert-success saving-alert saving-alert hidden"><?php echo esc_html__('Settings successfully saved!', 'xstore-core'); ?></p>
            <form class="xstore-panel-settings" method="post" data-in-popup="yes" data-post_id="<?php echo esc_attr($post->ID); ?>">
                <div class="et_panel-popup-inner with-scroll">
                    <div class="xstore-panel-settings-inner">
                        <?php require_once( ET_CORE_DIR . 'app/models/slides/template-parts/settings.php' ); ?>
                    </div>
                    <input type="hidden" name="etheme_<?php echo esc_attr($post_type) . '_save_settings_nonce'; ?>" value="<?php echo wp_create_nonce( 'etheme_'.$post_type.'_save_setting_nonce' ); ?>">
                </div>
                <br/>
                <br/>
                <button class="et-button et-elementor-editor-thumbnail-action full-width" type="submit" data-action="settings_save" style="pointer-events: none">
                    <?php echo esc_html__( 'Save', 'xstore-core' ); ?>
                    <span class="et-loader">
                    <svg class="loader-circular" viewBox="25 25 50 50">
                        <circle class="loader-path" cx="50" cy="50" r="12" fill="none" stroke-width="2"
                                stroke-miterlimit="10"></circle>
                    </svg>
                </span>
                </button>
            </form>
            <?php
            return ob_get_clean();
        }

    }
	$Etheme_Slides = new Etheme_Slides();
}
