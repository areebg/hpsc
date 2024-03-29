<?php
namespace ETC\App\Controllers\Elementor\Dynamic_Tags;

use ETC\App\Classes\Elementor;

class Sales_Booster_Fake_Sales extends \Elementor\Core\DynamicTags\Tag {

    public function get_name() {
        return 'etheme_sales_booster_fake_sales-tag';
    }

    public function get_title() {
        return __( 'Fake sales (Sales Booster)', 'xstore-core' );
    }

    public function get_group() {
        return \ElementorPro\Modules\Woocommerce\Module::WOOCOMMERCE_GROUP;// 'woocommerce'; // group key is taken from Elementor Pro code
    }

    public function get_categories() {
        return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
    }

    public function render() {
        global $product;

        $product = Elementor::get_product();

        if ( ! $product ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo Elementor::elementor_frontend_alert_message();
            }
            return '';
        }

        if ( !function_exists('etheme_get_fake_product_sales_count') ) return '';

        $rendered_string = etheme_get_fake_product_sales_count($product->get_ID());
        if ( $rendered_string ) {
            echo $rendered_string;
        }
    }

}