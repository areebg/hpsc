<?php
namespace ETC\App\Controllers;

use ETC\App\Controllers\Base_Controller;

/**
 * Create post type controller.
 *
 * @since      1.4.4
 * @package    ETC
 * @subpackage ETC/Models
 */
class Post_Types extends Base_Controller{

	public $domain = 'xstore-core';

    /**
     * Registered panels.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public static $post_args = NULL;

    /**
     * Registered panels.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public static $tax_args = NULL;

    /**
     * Register post args
     *
     * @return mixed|null|void
     */
    public static function register_post_args() {

        if ( ! is_null( self::$post_args ) ) {
            return self::$post_args;
        }

        return self::$post_args = !function_exists('etheme_is_activated') || !etheme_is_activated() ? array() : apply_filters( 'etc/add/post/args', array() );
    }

    /**
     * Register taxonomies args
     *
     * @return mixed|null|void
     */
    public static function register_taxonomies_args() {

        if ( ! is_null( self::$tax_args ) ) {
            return self::$tax_args;
        }

        return self::$tax_args = apply_filters( 'etc/add/tax/args', array() );
    }


	public function hooks() {

		add_action( 'init', array( $this, 'create_custom_post_types' ), 1 );
		add_action( 'init', array( $this, 'create_taxonomies' ), 1 );
        add_action('init', array($this, 'remove_frontend_actions'));
		add_filter( 'post_type_link', array( $this, 'portfolio_post_type_link' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'custom_type_settings' ) );
		add_action( 'load-options-permalink.php', array( $this,'seatings_for_permalink') );
		add_filter( 'manage_staticblocks_posts_columns', array( $this, 'et_staticblocks_columns' ) );
		add_action( 'manage_staticblocks_posts_custom_column', array( $this, 'et_staticblocks_columns_val' ), 10, 2 );

        add_filter( 'manage_etheme_slides_posts_columns', array( $this, 'etheme_slides_columns' ) );
        add_action( 'manage_etheme_slides_posts_custom_column', array( $this, 'etheme_slides_columns_val' ), 10, 2 );
        add_action( 'wp_ajax_et_etheme_slide_create', array( $this, 'create_etheme_slide' ) );
        add_action('admin_notices', array($this, 'etheme_slides_banner'), 500 );

		add_action( 'brand_add_form_fields', array( $this, 'add_brand_fileds') );
		add_action( 'brand_edit_form_fields', array( $this, 'edit_brand_fields' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'brand_admin_scripts' ) );
		add_action( 'created_term', array( $this, 'brands_fields_save' ), 10,3 );
		add_action( 'edit_term', array( $this, 'brands_fields_save' ), 10,3 );
	}

    /**
     * Create post types
     * @return null
     */
    public function create_custom_post_types() {
        $args = self::register_post_args();

        foreach ( $args as $fields ) {
            $this->get_model()->register_single_post_type( $fields );

        }

    }

    /**
     * Create post types
     * @return null
     */
    public function create_taxonomies() {
        $args = self::register_taxonomies_args();

        foreach ( $args as $fields ) {

            $this->get_model()->register_single_post_type_taxnonomy( $fields );

        }

    }

    public function remove_frontend_actions() {
        if (isset($_GET['et_iframe_preview'])) {
            $options = explode('|', $_GET['et_iframe_preview']);
            if ( in_array('admin_bar', $options) ) {
                // Send MIME Type header like WP admin-header.
                @header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

                add_filter('show_admin_bar', '__return_false');
            }
            if ( in_array('mobile_panel', $options) ) {
                add_filter('theme_mod_mobile_panel_et-mobile', '__return_false');
            }
            if ( in_array('back_top', $options) ) {
                add_filter('theme_mod_to_top', '__return_false');
                add_filter('theme_mod_to_top_mobile', '__return_false');
            }
            if ( in_array('header', $options) ) {
                remove_all_actions( 'etheme_header' );
                remove_all_actions( 'etheme_header_mobile' );
            }
            if ( in_array('footer', $options) ) {
                remove_all_actions('etheme_prefooter');
                remove_all_actions('etheme_footer');
            }

            remove_action('et_after_body', 'etheme_bordered_layout');
            remove_action('after_page_wrapper', 'etheme_photoswipe_template', 30);
            remove_action('after_page_wrapper', 'et_notify', 40);
            remove_action('after_page_wrapper', 'et_buffer', 40);

            add_filter('et_ajax_widgets', '__return_false');
            add_filter('etheme_ajaxify_lazyload_widget', '__return_false');
            add_filter('etheme_ajaxify_elementor_widget', '__return_false');

            // Handle `wp_enqueue_scripts`
//            remove_all_actions( 'wp_enqueue_scripts' );

            // Also remove all scripts hooked into after_wp_tiny_mce.
            remove_all_actions( 'after_wp_tiny_mce' );
            // Setup default heartbeat options
            add_filter( 'heartbeat_settings', function( $settings ) {
                $settings['interval'] = 15;
                return $settings;
            } );
            // Tell to WP Cache plugins do not cache this request.
            \Elementor\Utils::do_not_cache();
        }
    }

	public function portfolio_post_type_link( $permalink, $post ) {
		/**
		 *
		 * Add support for portfolio link custom structure.
		 *
		 */
		if ( $post->post_type != 'etheme_portfolio' ) {
			return $permalink;
		}


		if ( false === strpos( $permalink, '%' ) ) {
			return $permalink;
		}

		// Get the custom taxonomy terms of this post.
		$terms = get_the_terms( $post->ID, 'portfolio_category' );

		if ( ! empty( $terms ) ) {
			$terms = wp_list_sort( $terms, 'ID' );  // order by ID

			$category_object = apply_filters( 'portfolio_post_type_link_portfolio_cat', $terms[0], $terms, $post );
			$category_object = get_term( $category_object, 'portfolio_category' );
			$portfolio_category     = $category_object->slug;

			if ( $category_object->parent ) {
				$ancestors = get_ancestors( $category_object->term_id, 'portfolio_category' );
				foreach ( $ancestors as $ancestor ) {
					$ancestor_object = get_term( $ancestor, 'portfolio_category' );
					$portfolio_category     = $ancestor_object->slug . '/' . $portfolio_category;
				}
			}
		} else {
			$portfolio_category = esc_html__( 'uncategorized', 'xstore-core' );
		}

		if ( strpos( $permalink, '%author%' ) != false ) {
			$authordata = get_userdata( $post->post_author );
			$author = $authordata->user_nicename;
		} else {
			$author = '';
		}

		$find = array(
			'%year%',
			'%monthnum%',
			'%day%',
			'%hour%',
			'%minute%',
			'%second%',
			'%post_id%',
			'%author%',
			'%category%',
			'%portfolio_category%'
		);

		$replace = array(
			date_i18n( 'Y', strtotime( $post->post_date ) ),
			date_i18n( 'm', strtotime( $post->post_date ) ),
			date_i18n( 'd', strtotime( $post->post_date ) ),
			date_i18n( 'H', strtotime( $post->post_date ) ),
			date_i18n( 'i', strtotime( $post->post_date ) ),
			date_i18n( 's', strtotime( $post->post_date ) ),
			$post->ID,
			$author,
			$portfolio_category,
			$portfolio_category
		);

		$permalink = str_replace( $find, $replace, $permalink );

		return $permalink;
	}

	public function et_staticblocks_columns($defaults) {
	    return array(
	    	'cb'               => '<input type="checkbox" />',
	        'title'            => esc_html__( 'Title', 'xstore-core' ),
	        'shortcode_column' => esc_html__( 'Shortcode', 'xstore-core' ),
	        'date'             => esc_html__( 'Date', 'xstore-core' ),
	    );
	}
	 
	public function et_staticblocks_columns_val($column_name, $post_ID) {
	   if ($column_name == 'shortcode_column') { ?>
           <div class="staticblock-copy-code">
               <button class="button button-small copy-staticblock-code" type="button" data-text="<?php esc_html_e('Copy shortcode', 'xstore-core') ?>" data-success-text="<?php esc_html_e('Successfully copied!', 'xstore-core') ?>"><?php esc_html_e('Copy shortcode', 'xstore-core') ?></button>
               <pre>[block id="<?php echo $post_ID; ?>"]</pre>
           </div>
	   <?php }
	}

    public function etheme_slides_columns($defaults) {
        return array(
            'cb'               => '<input type="checkbox" />',
            'thumbnail' => esc_html__( 'Thumbnail', 'xstore-core' ),
            'title'            => esc_html__( 'Title', 'xstore-core' ),
            'date'             => esc_html__( 'Date', 'xstore-core' ),
        );
    }

    public function etheme_slides_columns_val($column_name, $post_ID) {
        if ($column_name == 'thumbnail') {
            $args = array(
                'admin_bar',
                'mobile_panel',
                'mobile_panel',
                'back_top',
                'header',
                'footer');
            $has_content = get_the_content(null, false, $post_ID);
            ?>
            <div class="etheme-slides-previewer<?php if ( $has_content ) : ?> mtips mtips-right mtips-img mtips-lg<?php endif; ?>">
                <a href="<?php echo admin_url('post.php?post='.$post_ID.'&action=elementor'); ?>">
                    <?php
                    if ( has_post_thumbnail() )
                        the_post_thumbnail( 'full' );
                    else { ?>
                        <img src="<?php echo ETHEME_CODE_IMAGES . 'placeholder.jpg'; ?>" alt="<?php echo esc_attr__('Slide placeholder', 'xstore-core'); ?>">
                    <?php } ?>
                </a>
                <?php if ( $has_content ) : ?>
                    <span class="mt-mes">
                        <iframe class="loading" data-src="<?php echo add_query_arg('et_iframe_preview', implode('|', $args), get_permalink($post_ID)); ?>" frameborder="0"></iframe>
                    </span>
                <?php endif; ?>
            </div>
            <?php // echo '<div class="etheme-slides-thumb">'.get_the_post_thumbnail( $post_ID, 'thumbnail' ).'</div>'; ?>
        <?php }
    }

    public function create_etheme_slide() {
        // get all slides to set new slide item number on creation
        $created_templates = array( 'post_type' => 'etheme_slides', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' );
        $created_templates = count(get_posts( $created_templates ) );

        $post_args = array(
            'post_title' => sprintf(esc_html__('Slide %s', 'xstore-core'), ($created_templates+1)),
            'post_type'  => 'etheme_slides',
        );

        $post_id = wp_insert_post( $post_args );

        $url = add_query_arg(
            array(
                'post'           => $post_id,
                'action'         => 'elementor',
                'classic-editor' => '',
                'et_open_etheme-slides-import' => 'yes',
            ),
            admin_url( 'post.php' )
        );

        wp_send_json(
            array(
                'redirect_url' => $url,
            )
        );
    }

    public function etheme_slides_banner() {
        if ( isset($_GET['post_type']) && $_GET['post_type'] == 'etheme_slides' ) {
            $video_id = 'i7STFGZapx8';
            $xstore_branding_settings = get_option( 'xstore_white_label_branding_settings', array() ); ?>
            <div class="wrap">
                <div class="<?php echo esc_attr($_GET['post_type']); ?>-banner flex">
            <div class="<?php echo esc_attr($_GET['post_type']); ?>-banner-info">
                <div class="logo">
                    <?php
                    if ( isset( $xstore_branding_settings['control_panel']['logo'] ) && ! empty( $xstore_branding_settings['control_panel']['logo'] ) ) : ?>
                        <img src="<?php echo esc_url( $xstore_branding_settings['control_panel']['logo'] ); ?>" alt="panel-logo">
                    <?php else: ?>
                        <svg width="237" height="43" viewBox="0 0 237 43" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <path d="M45.344 13.238C45.236 13.418 45.122 13.556 45.002 13.652C44.882 13.736 44.726 13.778 44.534 13.778C44.33 13.778 44.09 13.676 43.814 13.472C43.55 13.268 43.208 13.046 42.788 12.806C42.38 12.554 41.882 12.326 41.294 12.122C40.718 11.918 40.016 11.816 39.188 11.816C38.408 11.816 37.718 11.924 37.118 12.14C36.518 12.344 36.014 12.626 35.606 12.986C35.21 13.346 34.91 13.772 34.706 14.264C34.502 14.744 34.4 15.266 34.4 15.83C34.4 16.55 34.574 17.15 34.922 17.63C35.282 18.098 35.75 18.5 36.326 18.836C36.914 19.172 37.574 19.466 38.306 19.718C39.05 19.958 39.806 20.21 40.574 20.474C41.354 20.738 42.11 21.038 42.842 21.374C43.586 21.698 44.246 22.112 44.822 22.616C45.41 23.12 45.878 23.738 46.226 24.47C46.586 25.202 46.766 26.102 46.766 27.17C46.766 28.298 46.574 29.36 46.19 30.356C45.806 31.34 45.242 32.198 44.498 32.93C43.766 33.662 42.86 34.238 41.78 34.658C40.712 35.078 39.494 35.288 38.126 35.288C36.458 35.288 34.934 34.988 33.554 34.388C32.186 33.776 31.016 32.954 30.044 31.922L31.052 30.266C31.148 30.134 31.262 30.026 31.394 29.942C31.538 29.846 31.694 29.798 31.862 29.798C32.114 29.798 32.402 29.936 32.726 30.212C33.05 30.476 33.452 30.77 33.932 31.094C34.424 31.418 35.012 31.718 35.696 31.994C36.392 32.258 37.238 32.39 38.234 32.39C39.062 32.39 39.8 32.276 40.448 32.048C41.096 31.82 41.642 31.502 42.086 31.094C42.542 30.674 42.89 30.176 43.13 29.6C43.37 29.024 43.49 28.382 43.49 27.674C43.49 26.894 43.31 26.258 42.95 25.766C42.602 25.262 42.14 24.842 41.564 24.506C40.988 24.17 40.328 23.888 39.584 23.66C38.852 23.42 38.096 23.18 37.316 22.94C36.548 22.688 35.792 22.4 35.048 22.076C34.316 21.752 33.662 21.332 33.086 20.816C32.51 20.3 32.042 19.658 31.682 18.89C31.334 18.11 31.16 17.15 31.16 16.01C31.16 15.098 31.334 14.216 31.682 13.364C32.042 12.512 32.558 11.756 33.23 11.096C33.902 10.436 34.73 9.908 35.714 9.512C36.71 9.116 37.85 8.918 39.134 8.918C40.574 8.918 41.888 9.146 43.076 9.602C44.264 10.058 45.302 10.718 46.19 11.582L45.344 13.238ZM68.7898 9.206V12.14H60.4558V35H56.9638V12.14H48.5938V9.206H68.7898ZM94.6564 22.112C94.6564 24.044 94.3504 25.82 93.7384 27.44C93.1264 29.048 92.2624 30.434 91.1464 31.598C90.0304 32.762 88.6864 33.668 87.1144 34.316C85.5544 34.952 83.8264 35.27 81.9304 35.27C80.0344 35.27 78.3064 34.952 76.7464 34.316C75.1864 33.668 73.8484 32.762 72.7324 31.598C71.6164 30.434 70.7524 29.048 70.1404 27.44C69.5284 25.82 69.2224 24.044 69.2224 22.112C69.2224 20.18 69.5284 18.41 70.1404 16.802C70.7524 15.182 71.6164 13.79 72.7324 12.626C73.8484 11.45 75.1864 10.538 76.7464 9.89C78.3064 9.242 80.0344 8.918 81.9304 8.918C83.8264 8.918 85.5544 9.242 87.1144 9.89C88.6864 10.538 90.0304 11.45 91.1464 12.626C92.2624 13.79 93.1264 15.182 93.7384 16.802C94.3504 18.41 94.6564 20.18 94.6564 22.112ZM91.0744 22.112C91.0744 20.528 90.8584 19.106 90.4264 17.846C89.9944 16.586 89.3824 15.524 88.5904 14.66C87.7984 13.784 86.8384 13.112 85.7104 12.644C84.5824 12.176 83.3224 11.942 81.9304 11.942C80.5504 11.942 79.2964 12.176 78.1684 12.644C77.0404 13.112 76.0744 13.784 75.2704 14.66C74.4784 15.524 73.8664 16.586 73.4344 17.846C73.0024 19.106 72.7864 20.528 72.7864 22.112C72.7864 23.696 73.0024 25.118 73.4344 26.378C73.8664 27.626 74.4784 28.688 75.2704 29.564C76.0744 30.428 77.0404 31.094 78.1684 31.562C79.2964 32.018 80.5504 32.246 81.9304 32.246C83.3224 32.246 84.5824 32.018 85.7104 31.562C86.8384 31.094 87.7984 30.428 88.5904 29.564C89.3824 28.688 89.9944 27.626 90.4264 26.378C90.8584 25.118 91.0744 23.696 91.0744 22.112ZM103.255 24.236V35H99.7811V9.206H107.071C108.703 9.206 110.113 9.374 111.301 9.71C112.489 10.034 113.467 10.508 114.235 11.132C115.015 11.756 115.591 12.512 115.963 13.4C116.335 14.276 116.521 15.26 116.521 16.352C116.521 17.264 116.377 18.116 116.089 18.908C115.801 19.7 115.381 20.414 114.829 21.05C114.289 21.674 113.623 22.208 112.831 22.652C112.051 23.096 111.163 23.432 110.167 23.66C110.599 23.912 110.983 24.278 111.319 24.758L118.843 35H115.747C115.111 35 114.643 34.754 114.343 34.262L107.647 25.046C107.443 24.758 107.221 24.554 106.981 24.434C106.741 24.302 106.381 24.236 105.901 24.236H103.255ZM103.255 21.698H106.909C107.929 21.698 108.823 21.578 109.591 21.338C110.371 21.086 111.019 20.738 111.535 20.294C112.063 19.838 112.459 19.298 112.723 18.674C112.987 18.05 113.119 17.36 113.119 16.604C113.119 15.068 112.609 13.91 111.589 13.13C110.581 12.35 109.075 11.96 107.071 11.96H103.255V21.698ZM138.483 9.206V12.05H126.099V20.618H136.125V23.354H126.099V32.156H138.483V35H122.589V9.206H138.483Z" fill="white"/>
                            <rect y="9" width="28" height="26" fill="url(#pattern0)"/>
                            <path opacity="0.7" d="M163.092 12.626C162.984 12.83 162.828 12.932 162.624 12.932C162.468 12.932 162.264 12.824 162.012 12.608C161.772 12.38 161.442 12.134 161.022 11.87C160.602 11.594 160.074 11.342 159.438 11.114C158.814 10.886 158.04 10.772 157.116 10.772C156.192 10.772 155.376 10.904 154.668 11.168C153.972 11.432 153.384 11.792 152.904 12.248C152.436 12.704 152.076 13.232 151.824 13.832C151.584 14.432 151.464 15.062 151.464 15.722C151.464 16.586 151.644 17.3 152.004 17.864C152.376 18.428 152.862 18.908 153.462 19.304C154.062 19.7 154.74 20.036 155.496 20.312C156.264 20.576 157.05 20.84 157.854 21.104C158.658 21.368 159.438 21.662 160.194 21.986C160.962 22.298 161.646 22.694 162.246 23.174C162.846 23.654 163.326 24.248 163.686 24.956C164.058 25.652 164.244 26.522 164.244 27.566C164.244 28.634 164.058 29.642 163.686 30.59C163.326 31.526 162.798 32.342 162.102 33.038C161.406 33.734 160.554 34.286 159.546 34.694C158.538 35.09 157.386 35.288 156.09 35.288C154.41 35.288 152.964 34.994 151.752 34.406C150.54 33.806 149.478 32.99 148.566 31.958L149.07 31.166C149.214 30.986 149.382 30.896 149.574 30.896C149.682 30.896 149.82 30.968 149.988 31.112C150.156 31.256 150.36 31.436 150.6 31.652C150.84 31.856 151.128 32.084 151.464 32.336C151.8 32.576 152.19 32.804 152.634 33.02C153.078 33.224 153.588 33.398 154.164 33.542C154.74 33.686 155.394 33.758 156.126 33.758C157.134 33.758 158.034 33.608 158.826 33.308C159.618 32.996 160.284 32.576 160.824 32.048C161.376 31.52 161.796 30.896 162.084 30.176C162.372 29.444 162.516 28.664 162.516 27.836C162.516 26.936 162.33 26.198 161.958 25.622C161.598 25.034 161.118 24.548 160.518 24.164C159.918 23.768 159.234 23.438 158.466 23.174C157.71 22.91 156.93 22.652 156.126 22.4C155.322 22.148 154.536 21.866 153.768 21.554C153.012 21.242 152.334 20.846 151.734 20.366C151.134 19.874 150.648 19.268 150.276 18.548C149.916 17.816 149.736 16.904 149.736 15.812C149.736 14.96 149.898 14.138 150.222 13.346C150.546 12.554 151.02 11.858 151.644 11.258C152.268 10.646 153.036 10.16 153.948 9.8C154.872 9.428 155.922 9.242 157.098 9.242C158.418 9.242 159.6 9.452 160.644 9.872C161.7 10.292 162.66 10.934 163.524 11.798L163.092 12.626ZM170.939 8.81V35H169.229V8.81H170.939ZM179.482 17.09V35H177.772V17.09H179.482ZM180.238 10.916C180.238 11.132 180.19 11.336 180.094 11.528C180.01 11.708 179.896 11.87 179.752 12.014C179.608 12.158 179.44 12.272 179.248 12.356C179.056 12.44 178.852 12.482 178.636 12.482C178.42 12.482 178.216 12.44 178.024 12.356C177.832 12.272 177.664 12.158 177.52 12.014C177.376 11.87 177.262 11.708 177.178 11.528C177.094 11.336 177.052 11.132 177.052 10.916C177.052 10.7 177.094 10.496 177.178 10.304C177.262 10.1 177.376 9.926 177.52 9.782C177.664 9.638 177.832 9.524 178.024 9.44C178.216 9.356 178.42 9.314 178.636 9.314C178.852 9.314 179.056 9.356 179.248 9.44C179.44 9.524 179.608 9.638 179.752 9.782C179.896 9.926 180.01 10.1 180.094 10.304C180.19 10.496 180.238 10.7 180.238 10.916ZM198.501 35C198.201 35 198.027 34.844 197.979 34.532L197.799 31.706C197.007 32.786 196.077 33.644 195.009 34.28C193.953 34.916 192.765 35.234 191.445 35.234C189.249 35.234 187.533 34.472 186.297 32.948C185.073 31.424 184.461 29.138 184.461 26.09C184.461 24.782 184.629 23.564 184.965 22.436C185.313 21.296 185.817 20.312 186.477 19.484C187.149 18.644 187.965 17.984 188.925 17.504C189.897 17.024 191.013 16.784 192.273 16.784C193.485 16.784 194.535 17.012 195.423 17.468C196.311 17.912 197.079 18.566 197.727 19.43V8.81H199.455V35H198.501ZM191.931 33.848C193.119 33.848 194.193 33.542 195.153 32.93C196.113 32.318 196.971 31.466 197.727 30.374V20.924C197.031 19.904 196.263 19.184 195.423 18.764C194.595 18.344 193.653 18.134 192.597 18.134C191.541 18.134 190.617 18.326 189.825 18.71C189.033 19.094 188.367 19.64 187.827 20.348C187.299 21.044 186.897 21.884 186.621 22.868C186.357 23.84 186.225 24.914 186.225 26.09C186.225 28.754 186.717 30.716 187.701 31.976C188.685 33.224 190.095 33.848 191.931 33.848ZM212.213 16.802C213.221 16.802 214.151 16.976 215.003 17.324C215.867 17.672 216.611 18.182 217.235 18.854C217.871 19.514 218.363 20.33 218.711 21.302C219.071 22.274 219.251 23.39 219.251 24.65C219.251 24.914 219.209 25.094 219.125 25.19C219.053 25.286 218.933 25.334 218.765 25.334L205.823 25.334V25.676C205.823 27.02 205.979 28.202 206.291 29.222C206.603 30.242 207.047 31.1 207.623 31.796C208.199 32.48 208.895 32.996 209.711 33.344C210.527 33.692 211.439 33.866 212.447 33.866C213.347 33.866 214.127 33.77 214.787 33.578C215.447 33.374 215.999 33.152 216.443 32.912C216.899 32.66 217.259 32.438 217.523 32.246C217.787 32.042 217.979 31.94 218.099 31.94C218.255 31.94 218.375 32 218.459 32.12L218.927 32.696C218.639 33.056 218.255 33.392 217.775 33.704C217.307 34.016 216.779 34.286 216.191 34.514C215.615 34.73 214.991 34.904 214.319 35.036C213.659 35.168 212.993 35.234 212.321 35.234C211.097 35.234 209.981 35.024 208.973 34.604C207.965 34.172 207.101 33.548 206.381 32.732C205.661 31.916 205.103 30.92 204.707 29.744C204.323 28.556 204.131 27.2 204.131 25.676C204.131 24.392 204.311 23.21 204.671 22.13C205.043 21.038 205.571 20.102 206.255 19.322C206.951 18.53 207.797 17.912 208.793 17.468C209.801 17.024 210.941 16.802 212.213 16.802ZM212.231 18.08C211.307 18.08 210.479 18.224 209.747 18.512C209.015 18.8 208.379 19.214 207.839 19.754C207.311 20.294 206.879 20.942 206.543 21.698C206.219 22.454 206.003 23.3 205.895 24.236L217.703 24.236C217.703 23.276 217.571 22.418 217.307 21.662C217.043 20.894 216.671 20.246 216.191 19.718C215.711 19.19 215.135 18.788 214.463 18.512C213.791 18.224 213.047 18.08 212.231 18.08ZM233.714 19.304C233.63 19.472 233.498 19.556 233.318 19.556C233.186 19.556 233.012 19.484 232.796 19.34C232.592 19.184 232.316 19.016 231.968 18.836C231.632 18.644 231.212 18.476 230.708 18.332C230.216 18.176 229.61 18.098 228.89 18.098C228.242 18.098 227.648 18.194 227.108 18.386C226.58 18.566 226.124 18.812 225.74 19.124C225.368 19.436 225.074 19.802 224.858 20.222C224.654 20.63 224.552 21.062 224.552 21.518C224.552 22.082 224.696 22.55 224.984 22.922C225.272 23.294 225.65 23.612 226.118 23.876C226.586 24.14 227.114 24.368 227.702 24.56C228.302 24.752 228.914 24.944 229.538 25.136C230.162 25.328 230.768 25.544 231.356 25.784C231.956 26.012 232.49 26.3 232.958 26.648C233.426 26.996 233.804 27.422 234.092 27.926C234.38 28.43 234.524 29.042 234.524 29.762C234.524 30.542 234.38 31.268 234.092 31.94C233.816 32.612 233.408 33.194 232.868 33.686C232.34 34.178 231.686 34.568 230.906 34.856C230.126 35.144 229.238 35.288 228.242 35.288C226.982 35.288 225.896 35.09 224.984 34.694C224.072 34.286 223.256 33.758 222.536 33.11L222.95 32.498C223.01 32.402 223.076 32.33 223.148 32.282C223.22 32.234 223.322 32.21 223.454 32.21C223.61 32.21 223.802 32.306 224.03 32.498C224.258 32.69 224.552 32.9 224.912 33.128C225.284 33.344 225.74 33.548 226.28 33.74C226.832 33.932 227.51 34.028 228.314 34.028C229.07 34.028 229.736 33.926 230.312 33.722C230.888 33.506 231.368 33.218 231.752 32.858C232.136 32.498 232.424 32.078 232.616 31.598C232.82 31.106 232.922 30.59 232.922 30.05C232.922 29.45 232.778 28.952 232.49 28.556C232.202 28.16 231.824 27.824 231.356 27.548C230.888 27.272 230.354 27.038 229.754 26.846C229.166 26.654 228.554 26.462 227.918 26.27C227.294 26.078 226.682 25.868 226.082 25.64C225.494 25.412 224.966 25.124 224.498 24.776C224.03 24.428 223.652 24.008 223.364 23.516C223.076 23.012 222.932 22.388 222.932 21.644C222.932 21.008 223.07 20.396 223.346 19.808C223.622 19.22 224.012 18.704 224.516 18.26C225.032 17.816 225.656 17.462 226.388 17.198C227.12 16.934 227.942 16.802 228.854 16.802C229.946 16.802 230.912 16.958 231.752 17.27C232.604 17.582 233.384 18.062 234.092 18.71L233.714 19.304Z" fill="white"/>
                            <defs>
                                <pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1">
                                    <use xlink:href="#image0_2885_13" transform="matrix(0.00769231 0 0 0.00828402 0 -0.00532544)"/>
                                </pattern>
                                <image id="image0_2885_13" width="130" height="122" xlink:href="data:image/png;base64,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"/>
                            </defs>
                        </svg>
                    <?php endif; ?>
                </div>
                <ol>
                    <?php
                        foreach (array(
                                sprintf(esc_html__('Start by %screating%s a new slide in this section.', 'xstore-core'), '<a href="'.admin_url('post-new.php?post_type=etheme_slides').'" target="_blank"">', '</a>'),
                                sprintf(esc_html__('Go to the %spages%s and edit using the Elementor Page Builder.', 'xstore-core'), '<a href="'.admin_url('edit.php?post_type=page').'" target="_blank"">', '</a>'),
                                sprintf(esc_html__('Choose the "%s" widget.', 'xstore-core'), '<a href="'.etheme_documentation_url(false, false).'" target="_blank"">' . sprintf(esc_html__('%s Slider', 'xstore-core'), apply_filters('etheme_theme_label', 'XStore')) . '</a>'),
                                 esc_html__('Select your pre-made slides created in the XStore Slides section.', 'xstore-core'),
                        ) as $banner_item ) {
                            echo '<li>'.$banner_item.'</li>';
                        }
                    ?>
                </ol>
            </div>
            <div class="<?php echo esc_attr($_GET['post_type']); ?>-banner-tutorial">
                <span class="play-icon">
                    <svg width="74" height="56" viewBox="0 0 74 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M72.2414 45.888C71.5042 49.7581 68.3713 52.7067 64.5013 53.2596C58.4197 54.1811 48.2838 55.2868 36.8579 55.2868C25.6162 55.2868 15.4803 54.1811 9.21446 53.2596C5.34439 52.7067 2.21147 49.7581 1.47431 45.888C0.737157 41.6494 0 35.3835 0 27.6434C0 19.9032 0.737157 13.6374 1.47431 9.39875C2.21147 5.52868 5.34439 2.58005 9.21446 2.02718C15.296 1.10574 25.4319 0 36.8579 0C48.2838 0 58.2354 1.10574 64.5013 2.02718C68.3713 2.58005 71.5042 5.52868 72.2414 9.39875C72.9786 13.6374 73.9 19.9032 73.9 27.6434C73.7157 35.3835 72.9786 41.6494 72.2414 45.888Z" fill="#EB3324"/>
                        <path d="M29.4863 40.5437V14.7432L51.601 27.6434L29.4863 40.5437Z" fill="white"/>
                    </svg>
                </span>
                <img src="https://img.youtube.com/vi/<?php echo esc_attr($video_id); ?>/maxresdefault.jpg" alt="<?php echo esc_attr__('Slide placeholder', 'xstore-core'); ?>">
            </div>
        </div>
            </div>
            <script id="<?php echo esc_attr($_GET['post_type']); ?>-banner-js">
                jQuery(document).ready(function ($) {
                    $('#wpwrap').prepend('<div class="et_panel-popup"></div>');
                   let banner = $(".<?php echo esc_js($_GET['post_type']) ?>-banner");
                   let banner_tutorial = $(".<?php echo esc_js($_GET['post_type']) ?>-banner-tutorial");
                    banner_tutorial.on('click', function () {
                        let popup = $(document).find('.et_panel-popup');
                        $('body').addClass('et_panel-popup-on');

                        popup.addClass('auto-size').html('<iframe width="888" height="500" src="https://www.youtube.com/embed/<?php echo esc_js($video_id); ?>?controls=1&showinfo=0&controls=0&rel=0&autoplay=1&start=0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>');
                        popup.prepend('<span class="et_close-popup et-button-cancel et-button"><i class="et-admin-icon et-delete"></i></span>');
                        $('.et_panel-popup').addClass('active');
                    });
                //    $("#<?php //echo esc_js($_GET['post_type']) ?>//-banner-js").remove();
                });
            </script>
        <?php }
    }

	public function custom_type_settings() {

		/**
		 *
		 * Add Etheme section block to permalink setting page.
		 *
		 */
		if( get_theme_mod('portfolio_projects', true) || get_theme_mod('enable_brands', true) ){
			add_settings_section(
				'et_section',
				esc_html__( '8theme permalink settings' , 'xstore-core' ),
				array( $this, 'section_callback' ),
				'permalink'
			);
		}

		/**
		 *
		 * Add "Brand base" setting field to Etheme section block.
		 *
		 */
		if ( class_exists('Woocommerce') && get_theme_mod('enable_brands', true) ) {
			add_settings_field(
				'brand_base',
				esc_html__( 'Brand base' , 'xstore-core' ),
				array( $this, 'brand_callback' ),
				'permalink',
				'optional'
			);
		}

		if( get_theme_mod('portfolio_projects', true) ){
			/**
			 *
			 * Add "Portfolio base" setting field to Etheme section block.
			 *
			 */
			add_settings_field(
				'portfolio_base',
				esc_html__( 'Portfolio base' , 'xstore-core' ),
				array( $this, 'portfolio_callback' ),
				'permalink',
				'optional'
			);

			/**
			 *
			 * Add "Portfolio category base" setting field to Etheme section block.
			 *
			 */
			add_settings_field(
				'portfolio_cat_base',
				esc_html__( 'Portfolio category base' , 'xstore-core' ),
				array( $this, 'portfolio_cat_callback' ),
				'permalink',
				'optional'
			);
		}
	}


	public function section_callback() {
		/**
		 *
		 * Callback function for Etheme section block.
		 *
		 */

		$checked['portfolio_def'] = ( get_option( 'et_permalink' ) == 'portfolio_def' || ! get_option( 'et_permalink' ) ) ? 'checked' : '';
		$checked['portfolio_cat_base'] = ( get_option( 'et_permalink' ) == 'portfolio_cat_base' ) ? 'checked' : '';
		$checked['portfolio_custom_base'] = ( get_option( 'et_permalink' ) == 'portfolio_custom_base' ) ? 'checked' : '';

		if ( class_exists('Woocommerce') && get_theme_mod('enable_brands', true) ) {
			$shop_url = get_permalink( wc_get_page_id( 'shop' ) ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url() . '/shop/';
			$checked['brand_def'] = ( get_option( 'et_brand_permalink' ) == 'brand_def' || ! get_option( 'et_brand_permalink' ) ) ? 'checked' : '';
			$checked['brand_shop_base'] = ( get_option( 'et_brand_permalink' ) == 'brand_shop_base' || ! get_option( 'et_brand_permalink' ) ) ? 'checked' : '';
			$checked['brand_custom_base'] = ( get_option( 'et_brand_permalink' ) == 'brand_custom_base' ) ? 'checked' : '';

			echo '
				<p>' . esc_html__( '8theme brand permalink settings.' , 'xstore-core' ) . '</p>
				</tbody></tr></th>
				<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><label><input class="et-inp-brand" type="radio" name="et_brand_permalink" value="brand_def" ' . $checked['brand_def'] . ' >' . esc_html__( 'Default' , 'xstore-core' ) . '</label></th>
								<td><code>' . esc_html( home_url() ) . '/brand-base/brand-archive/</code></td>
							</tr>
							<tr>
								<th scope="row"><label><input class="et-inp-brand" type="radio" name="et_brand_permalink" value="brand_shop_base" ' . $checked['brand_shop_base'] . '>' . esc_html__( 'Shop page base' , 'xstore-core' ) . '</label></th>
								<td><code>' . $shop_url . 'brand-base/brand-archive/</code></td>
								<input type="hidden" id="brand-custom-base" name="brand_custom_base" value="' . get_option( 'brand_custom_base' ) . '">
							</tr>
							
						</tbody>
				</table> 
			';
		}

		if( get_theme_mod('portfolio_projects', true) || get_theme_mod('enable_brands', true) ){
			echo '
				<p>' . __( '8theme portfolio permalink settings.' , 'xstore-core' ) . '</p>
				</tbody></tr></th>
				<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><label><input class="et-inp" type="radio" name="et_permalink" value="portfolio_def" ' . $checked['portfolio_def'] . ' >' . esc_html__( 'Default' , 'xstore-core' ) . '</label></th>
								<td><code>' . esc_html( home_url() ) . '/portfolio-base/sample-project/</code></td>
							</tr>
							<tr>
								<th scope="row"><label><input class="et-inp" type="radio" name="et_permalink" value="portfolio_cat_base" ' . $checked['portfolio_cat_base'] . '>' . esc_html__( 'Portfolio category base' , 'xstore-core' ) . '</label></th>
								<td><code>' . esc_html( home_url() ) . '/portfolio-base/portfolio-category/sample-project/</code></td>
							</tr>
							<tr>
								<th scope="row"><label><input id="portfolio-custom-base-select" type="radio" name="et_permalink" value="portfolio_custom_base" ' . $checked['portfolio_custom_base'] . '>' . esc_html__( 'Portfolio custom Base' , 'xstore-core' ) . '</label></th>
								<td><code>' . esc_html( home_url() ) . '/portfolio-base</code><input id="portfolio-custom-base" name="portfolio_custom_base" type="text" value="' . get_option( 'portfolio_custom_base' ) . '" class="regular-text code" /></td>
							</tr>
						</tbody>
				</table>

				<script type="text/javascript">
					jQuery( function() {
						jQuery("input.et-inp, input.et-inp-brand").change(function() {
							
							var link = "";

							if ( jQuery( this ).val() == "portfolio_cat_base" ) {
								link = "/%portfolio_category%";
							} else if ( jQuery( this ).val() == "brand_shop_base" ) {
								link = "' . basename( $shop_url ) . '";
							} else {
								link = "";
							}
							
							if ( jQuery( this ).is( ".et-inp-brand" ) ){
								jQuery("#brand-custom-base").val( link );
							} else {
								jQuery("#portfolio-custom-base").val( link );
							}
						});

						jQuery("input:checked").change();
						jQuery("#portfolio-custom-base").focus( function(){
							jQuery("#portfolio-custom-base-select").click();
						} );
					} );
				</script>

				'
			;
		}
	}


	public function portfolio_callback() {
		/**
		 *
		 * Callback function for "portfolio base" setting field.
		 *
		 */

		echo '<input 
			name="portfolio_base"  
			type="text" 
			value="' . get_option( 'portfolio_base' ) . '" 
			class="regular-text code"
			placeholder="project"
		 />';
	}

	public function brand_callback() {
		/**
		 *
		 * Callback function for "brand base" setting field.
		 *
		 */

		echo '<input 
			name="brand_base"  
			type="text" 
			value="' . get_option( 'brand_base' ) . '" 
			class="regular-text code"
			placeholder="brand"
		 />';
	}

	public function portfolio_cat_callback() {
		/**
		 *
		 * Callback function for "portfolio catogory base" setting field.
		 *
		 */

		echo '<input 
			name="portfolio_cat_base"  
			type="text" 
			value="' . get_option( 'portfolio_cat_base' ) . '" 
			class="regular-text code"
			placeholder="portfolio-category"
		 />';
	}


	public function seatings_for_permalink() {
		/**
		 *
		 * Make it work on permalink page.
		 *
		 */
		if ( ! is_admin() ) {
			return;
		}

		if( isset( $_POST['brand_base'] ) ) {
			update_option( 'brand_base', sanitize_title_with_dashes( $_POST['brand_base'] ) );
		}

		if( isset( $_POST['portfolio_base'] ) ) {
			update_option( 'portfolio_base', sanitize_title_with_dashes( $_POST['portfolio_base'] ) );
		}

		if( isset( $_POST['portfolio_cat_base'] ) ) {
			update_option( 'portfolio_cat_base', sanitize_title_with_dashes( $_POST['portfolio_cat_base'] ) );
		}

		if( isset( $_POST['et_permalink'] ) ) {
			update_option( 'et_permalink', sanitize_title_with_dashes( $_POST['et_permalink'] ) );
		}

		if( isset( $_POST['portfolio_custom_base'] ) ) {
			update_option( 'portfolio_custom_base', $_POST['portfolio_custom_base'] );
		}

		if( isset( $_POST['et_brand_permalink'] ) ) {
			update_option( 'et_brand_permalink', sanitize_title_with_dashes( $_POST['et_brand_permalink'] ) );
		}

		if( isset( $_POST['brand_custom_base'] ) ) {
			update_option( 'brand_custom_base', sanitize_title_with_dashes( $_POST['brand_custom_base'] ) );
		}
	}

	/**
	 * Product brands image filed description
	 * @return [type] [description]
	 */
	public function add_brand_fileds() {

		$this->view->add_brand_fileds(
			array(
				'thumbnail'   			  =>	esc_html__( 'Thumbnail', 'xstore-core' ),
				'upload'      			  =>	esc_html__( 'Upload/Add image', 'xstore-core' ),
				'remove'      			  =>	esc_html__( 'Remove image', 'xstore-core' ),
			)
		);

	}

	/**
	 * Product brands edit single tax image filed
	 * @param  [type] $term     [description]
	 * @param  [type] $taxonomy [description]
	 * @return [type]           [description]
	 */
	public function edit_brand_fields($term, $taxonomy ) {
    	$thumbnail_id 	= absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
    	
    	$image = ( $thumbnail_id ) ? wp_get_attachment_thumb_url( $thumbnail_id ) : wc_placeholder_img_src();

    	
		$this->view->edit_brand_fields(
			array(
				'thumbnail'   		=>	esc_html__( 'Thumbnail', 'xstore-core' ),
				'upload'      		=>	esc_html__( 'Upload/Add image', 'xstore-core' ),
				'remove'      		=>	esc_html__( 'Remove image', 'xstore-core' ),
				'thumbnail_id'      =>	$thumbnail_id,
				'image'      		=>	$image,
			)
		);
    }

    /**
     * Product brands enqueue media for image selector
     * @return [type] [description]
     */
	public function brand_admin_scripts() {
        $screen = get_current_screen();
        if ( in_array( $screen->id, array('edit-brand') ) ){
			wp_enqueue_media();
        }
    }

    /**
     * Product brands Save image fields
     * @param  [type] $term_id  [description]
     * @param  [type] $tt_id    [description]
     * @param  [type] $taxonomy [description]
     * @return [type]           [description]
     */
    public function brands_fields_save($term_id, $tt_id, $taxonomy ) {
    	if ( isset( $_POST['brand_thumbnail_id'] ) ){
    		if (function_exists( 'update_term_meta' )){
			    update_term_meta( $term_id, 'thumbnail_id', absint( $_POST['brand_thumbnail_id'] ), '' );
		    } else {
			    update_metadata( 'woocommerce_term', $term_id, 'thumbnail_id', absint( $_POST['brand_thumbnail_id'] ), '' );
		    }
    	}
    	delete_transient( 'wc_term_counts' );
    }
}