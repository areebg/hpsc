<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );
/**
 * Template "Maintenance mode" for 8theme dashboard.
 *
 * @since   8.1.2
 * @version 1.0.0
 */

$maintenance_mode_page_options = array();

$maintenance_mode_page_options['is_enabled'] = get_option('etheme_maintenance_mode', false);
?>

    <h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__('Maintenance Mode', 'xstore'); ?></h2>
    <p>
		<?php
            esc_html_e('Maintenance mode allows you to display a user-friendly notice to your visitors instead of a broken site during website maintenance. Build a sleek maintenance page that will be shown to your site visitors. Only registered users with sufficient privileges can view the front end. Switch it off when you\'re ready to relaunch the site.', 'xstore');
        ?>
    </p>
    <p>
        <?php
        echo sprintf(esc_html__('You can create the maintenance page from scratch in Dashboard > Pages > %s. Choose "Maintenance" from the "Template" list under "Page attributes." Alternatively, you can import the page from our demo using the XStore Control Panel > Import demos > %s demos.', 'xstore'),
            '<a href="'.admin_url('post-new.php?post_type=page').'" target="_blank">'.esc_html__('Add new', 'xstore').'</a>',
            '<a href="'.admin_url('admin.php?page=et-panel-demos&s=coming+soon').'" target="_blank">'.esc_html__('Coming Soon', 'xstore').'</a>');
        ?>
    </p>
    <p>
        <label class="et-panel-option-switcher<?php if ( $maintenance_mode_page_options['is_enabled']) { ?> switched<?php } ?>" for="et_maintenance_mode">
            <input type="checkbox" id="et_maintenance_mode" name="et_maintenance_mode" <?php if ( $maintenance_mode_page_options['is_enabled']) { ?>checked<?php } ?>>
            <span></span>
        </label>
    </p>

<?php if ( $maintenance_mode_page_options['is_enabled'] ) : ?>
    <p class="et-message">
		<?php echo esc_html__('Your maintenance mode is activated. Add maintenance page by clicking the button below.', 'xstore'); ?>
    </p>
    <a href="<?php echo admin_url( 'edit.php?post_type=page' ); ?>" class="et-button et-button-green no-loader" target="_blank">
		<?php esc_html_e('Go to Pages', 'xstore'); ?>
    </a>
<?php endif; ?>

<?php unset($maintenance_mode_page_options); ?>