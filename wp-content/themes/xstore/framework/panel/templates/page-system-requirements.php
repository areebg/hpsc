<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );
/**
 * Template "System requirements" for 8theme dashboard.
 *
 * @since   6.3.10
 * @version 1.0.0
 */

if (isset($_GET['et_clear_wc_system_status_theme_info'])){
	delete_transient( 'wc_system_status_theme_info' );
	wp_redirect('?page=et-panel-system-requirements');
	exit;
}

if (isset($_GET['et_clear_system_information'])){
    delete_transient( 'etheme_system_information' );
    delete_transient( 'etheme_system_requirements_test_result' );
    wp_redirect('?page=et-panel-system-requirements');
    exit;
}

$global_admin_class = EthemeAdmin::get_instance();
?>

<h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__('System Requirements', 'xstore'); ?>
    <a href="?page=et-panel-system-requirements&et_clear_system_information" class="et-button">
        <?php $global_admin_class->get_loader(); ?>
        <span class="dashicons dashicons-image-rotate"></span>
        <span><?php esc_html_e( 'Check again', 'xstore' ); ?></span>
    </a>
</h2>
<p>
    <?php echo esc_html__(' Before using the theme, make sure your server and WordPress meet the theme\'s requirements. You can handle these adjustments yourself or reach out to your hosting provider to request increases in the following minimums.', 'xstore'); ?>
</p>
<br/>
<?php
$system = new Etheme_System_Requirements();
    $system->html();
$result = $system->result();
?>

<br/>
<br/>

<h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__('Additional information', 'xstore'); ?></h2>
<br/>
    <?php
        $system->wp_html();
    ?>
<br/>
<br/>

<?php
$system->wp_active_plugins();
?>

<h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__('WooCommerce system info cache', 'xstore'); ?>
    <a href="?page=et-panel-system-requirements&et_clear_wc_system_status_theme_info" class="et-button">
        <?php $global_admin_class->get_loader(); ?>
        <span class="dashicons dashicons-image-rotate"></span>
        <span><?php esc_html_e( 'Clear cache', 'xstore' ); ?></span>
    </a>
</h2>
<p class="et-message et-info">
    <?php echo esc_html__('Please ensure that you clear the WooCommerce system cache after updating the theme, as this may cause outdated files to remain.', 'xstore'); ?>
</p>
<br/>
