<?php
namespace ETC\App\Controllers\Elementor\Dynamic_Tags;

use ETC\App\Classes\Elementor;

class Product_Brand_Name extends \Elementor\Core\DynamicTags\Data_Tag {

    public function get_name() {
        return 'etheme_product_brand_name-tag';
    }

    public function get_title() {
        return __( 'Product Brand Name', 'xstore-core' );
    }

    public function get_group() {
        return \ElementorPro\Modules\Woocommerce\Module::WOOCOMMERCE_GROUP;// 'woocommerce'; // group key is taken from Elementor Pro code
    }

    public function get_categories() {
        return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
    }

    public function get_value( array $options = [] ) {
        global $product;

        $product = Elementor::get_product();
        $edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();

        if ( ! $product ) {
            if ( $edit_mode ) {
                echo Elementor::elementor_frontend_alert_message();
            }
            return '';
        }

        $terms = wp_get_post_terms( $product->get_ID(), 'brand' );

        if ( count( $terms ) < 1 ) {
            return $edit_mode ? '<div class="elementor-panel-alert elementor-panel-alert-info">'.
                esc_html__('This product does not have brand set', 'xstore-core') .
                '</div>' : '';
        }

        return $terms[0]->name;

    }

}