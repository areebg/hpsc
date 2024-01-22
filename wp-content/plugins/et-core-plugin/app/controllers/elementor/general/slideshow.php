<?php
namespace ETC\App\Controllers\Elementor\General;

use ETC\App\Classes\Elementor;
/**
 * Slideshow widget.
 *
 * @since      5.2.6
 * @package    ETC
 * @subpackage ETC/Controllers/Elementor/General
 */
class Slideshow extends \ETC\App\Controllers\Elementor\General\Carousel_Anything {

	/**
	 * Get widget name.
	 *
	 * @since 5.2.6
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'etheme_slideshow';
	}
	
	/**
	 * Get widget title.
	 *
	 * @since 5.2.6
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return sprintf(__( '%s Slider', 'xstore-core' ), apply_filters('etheme_theme_label', 'XStore'));
	}

    /**
     * Register controls.
     *
     * @since 5.1.7
     * @access protected
     */
    protected function register_controls() {
        parent::register_controls();

        $this->start_injection( [
            'type' => 'section',
            'at'   => 'start',
            'of'   => 'section_items',
        ] );

        $this->add_control(
            'description',
            [
                'type'            => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => sprintf(esc_html__('Generate captivating slides effortlessly by navigating to Dashboard -> %s -> Add New. Each new slide mirrors a post, offering the flexibility to craft compelling content seamlessly through the website\'s page builder.'), '<a href="'.admin_url( 'edit.php?post_type=etheme_slides' ).'" target="_blank">'.esc_html__('Slides', 'xstore-core').'</a>'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->end_injection();

        $this->start_injection( [
            'type' => 'control',
            'at'   => 'after',
            'of'   => 'dots_position',
        ] );


        $this->add_responsive_control(
            'dots_align',
            [
                'label' =>	__( 'Dots Alignment', 'xstore-core' ),
                'type' 	=>	\Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left'    => [
                        'title' => __( 'Left', 'xstore-core' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __( 'Center', 'xstore-core' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __( 'Right', 'xstore-core' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_injection();

        // each slide widgets will have own animations and triggered on slide change in script
        $this->update_control('content_animation', [
            'type' => \Elementor\Controls_Manager::HIDDEN,
        ]);
        $this->remove_control('free_mode');
    }
    /**
     * Get all elementor page templates
     *
     * @return array
     */
    protected function get_saved_content_list() {
        return array(
            'etheme_slide' => __( 'Slides', 'xstore-core' )
        );
    }

}
