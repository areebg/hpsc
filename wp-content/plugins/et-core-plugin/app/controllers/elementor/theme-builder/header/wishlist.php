<?php
namespace ETC\App\Controllers\Elementor\Theme_Builder\Header;

/**
 * Add to cart widget.
 *
 * @since      5.2
 * @package    ETC
 * @subpackage ETC/Controllers/Elementor
 */
class Wishlist extends \Elementor\Widget_Base {
    
	/**
	 * Get widget name.
	 *
	 * @since 5.2
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'theme-etheme_wishlist';
	}

	/**
	 * Get widget title.
	 *
	 * @since 5.2
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Header Wishlist', 'xstore-core' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 5.2
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eight_theme-elementor-icon et-elementor-add-to-cart-button';
	}

	/**
	 * Get widget keywords.
	 *
	 * @since 5.2
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
        return [ 'woocommerce', 'shop', 'store', 'wishlist', 'button', 'list', 'product' ];
	}

    /**
     * Get widget categories.
     *
     * @since 5.2
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
    	return ['theme-elements'];
    }
	
	/**
	 * Get widget dependency.
	 *
	 * @since 5.2
	 * @access public
	 *
	 * @return array Widget dependency.
	 */
	public function get_style_depends() {
		return [ 'etheme-off-canvas', 'etheme-cart-widget' ];
	}

    /**
     * Get widget dependency.
     *
     * @since 4.1.4
     * @access public
     *
     * @return array Widget dependency.
     */
    public function get_script_depends() {
        return [ 'etheme_et_wishlist' ];
    }
	
	/**
	 * Help link.
	 *
	 * @since 4.1.5
	 *
	 * @return string
	 */
	public function get_custom_help_url() {
		return etheme_documentation_url('122-elementor-live-copy-option', false);
	}

	/**
	 * Register widget controls.
	 *
	 * @since 5.2
	 * @access protected
	 */
	protected function register_controls() {
        $this->start_controls_section(
            'section_menu_icon_content',
            [
                'label' => esc_html__( 'Menu Icon', 'xstore-core' ),
            ]
        );

        $this->add_control(
            'icon',
            [
                'label' => esc_html__( 'Icon', 'xstore-core' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'heart-light' => esc_html__( 'Heart', 'xstore-core' ) . ' ' . esc_html__( 'Light', 'xstore-core' ),
                    'heart-medium' => esc_html__( 'Heart', 'xstore-core' ) . ' ' . esc_html__( 'Medium', 'xstore-core' ),
                    'heart-solid' => esc_html__( 'Heart', 'xstore-core' ) . ' ' . esc_html__( 'Solid', 'xstore-core' ),
                    'basket-light' => esc_html__( 'Basket', 'xstore-core' ) . ' ' . esc_html__( 'Light', 'xstore-core' ),
                    'basket-medium' => esc_html__( 'Basket', 'xstore-core' ) . ' ' . esc_html__( 'Medium', 'xstore-core' ),
                    'basket-solid' => esc_html__( 'Basket', 'xstore-core' ) . ' ' . esc_html__( 'Solid', 'xstore-core' ),
                    'bag-light' => esc_html__( 'Bag', 'xstore-core' ) . ' ' . esc_html__( 'Light', 'xstore-core' ),
                    'bag-medium' => esc_html__( 'Bag', 'xstore-core' ) . ' ' . esc_html__( 'Medium', 'xstore-core' ),
                    'bag-solid' => esc_html__( 'Bag', 'xstore-core' ) . ' ' . esc_html__( 'Solid', 'xstore-core' ),
                    'custom' => esc_html__( 'Custom', 'xstore-core' ),
                ],
                'default' => 'cart-medium',
                'prefix_class' => 'toggle-icon--', // Prefix class not used anymore, but kept for BC reasons.
                'render_type' => 'template',
            ]
        );

        $this->add_control(
            'menu_icon_svg',
            [
                'label' => esc_html__( 'Custom Icon', 'xstore-core' ),
                'type' => \Elementor\Controls_Manager::ICONS,
                'fa4compatibility' => 'icon_active',
                'default' => [
                    'value' => 'fas fa-shopping-cart',
                    'library' => 'fa-solid',
                ],
                'skin_settings' => [
                    'inline' => [
                        'none' => [
                            'label' => 'None',
                        ],
                    ],
                ],
                'recommended' => [
                    'fa-solid' => [
                        'shopping-bag',
                        'shopping-basket',
                        'shopping-cart',
                        'cart-arrow-down',
                        'cart-plus',
                    ],
                ],
                'skin' => 'inline',
                'label_block' => false,
                'condition' => [
                    'icon' => 'custom',
                ],
            ]
        );
        
		$this->end_controls_section();
		
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 5.2
	 * @access protected
	 */
	protected function render() {

		if ( !class_exists('WooCommerce') ) {
			echo esc_html__('Install WooCommerce Plugin to use this widget', 'xstore-core');
			return;
		}

        etheme_header_parts_wishlist();
		return;
		
		$settings = $this->get_settings_for_display();
		
		$this->add_render_attribute( 'wrapper', 'class', 'elementor-button-wrapper' );

        $element_options = array();
        $element_options['built_in_wishlist'] = get_theme_mod('xstore_wishlist', false);

        if ( !$element_options['built_in_wishlist'] ) {
            return '<div class="elementor-panel-alert elementor-panel-alert-warning">' .
                esc_html__('Install activate Wishlist to use this widget', 'xstore-core') .
                '</div>';
        }
		?>
		<div class="etheme-wc-add-to-cart-wrapper">
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
                <?php echo 'Wishlist widget here'; ?>
                <div class="et_element et_b_header-wishlist">
                    <?php echo header_wishlist_callback(); ?>
                </div>
            </div>
		</div>
		<?php
	}

    public static function render_menu_wishlist_toggle_button( $settings ) {
        if ( null === WC()->cart ) {
            return;
        }
        $product_count = WC()->cart->get_cart_contents_count();
        $sub_total = WC()->cart->get_cart_subtotal();
        $icon = ! empty( $settings['icon'] ) ? $settings['icon'] : 'cart-medium';
        ?>
        <div class="elementor-menu-cart__toggle elementor-button-wrapper">
            <a id="elementor-menu-cart__toggle_button" href="#" class="elementor-menu-cart__toggle_button elementor-button elementor-size-sm" aria-expanded="false">
                <span class="elementor-button-text"><?php echo $sub_total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                <span class="elementor-button-icon">
					<span class="elementor-button-icon-qty" data-counter="<?php echo esc_attr( $product_count ); ?>"><?php echo $product_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php
                    self::render_menu_icon( $settings, $icon );
                    ?>
					<span class="elementor-screen-only"><?php esc_html_e( 'Cart', 'xstore-core' ); ?></span>
				</span>
            </a>
        </div>
        <?php
    }

	public function unescape_html( $safe_text, $text ) {
		return $text;
	}
	
	protected function render_icon($settings) {
		$migrated = isset( $settings['__fa4_migrated']['selected_icon'] );
		$is_new = empty( $settings['icon'] ) && \Elementor\Icons_Manager::is_migration_allowed();
		if ( ! empty( $settings['icon'] ) || ! empty( $settings['selected_icon']['value'] ) ) : ?>
			<?php if ( $is_new || $migrated ) :
				\Elementor\Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
			else : ?>
                <i class="<?php echo esc_attr( $settings['icon'] ); ?>" aria-hidden="true"></i>
			<?php endif;
		endif;
	}

}
