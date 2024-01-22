<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );
/**
 * Template "Navigation" for 8theme dashboard.
 *
 * @since   6.0.2
 * @version 1.0.2
 */

$mtips_notify = esc_html__('Register your theme and activate XStore Core plugin, please.', 'xstore');
$theme_active = etheme_is_activated();
$core_active = class_exists('ETC\App\Controllers\Admin\Import');
$amp_active = class_exists('XStore_AMP');
$is_elementor = defined( 'ELEMENTOR_VERSION' );
$kirki_exists = class_exists( 'Kirki' );

$system_requirements = $plugins = $theme_options = '';

$system_requirements_active = false;
$xstore_branding_settings = get_option( 'xstore_white_label_branding_settings', array() );

$branding_label = 'XStore';
$custom_plugins_label = 'XStore';
$show_pages = array(
    'welcome',
    'system_requirements',
    'demos',
    'plugins',
    'patcher',
    'open_ai',
    'customize',
    'email_builder',
    'sales_booster',
    'custom_fonts',
    'maintenance_mode',
    'social_authentication',
    'social',
    'support',
    'changelog',
);

$hide_theme_builders = false;

if ( count($xstore_branding_settings) ) {
    if ( isset($xstore_branding_settings['control_panel']) ) {
        if ($xstore_branding_settings['control_panel']['label'])
            $branding_label = $xstore_branding_settings['control_panel']['label'];
        if (isset($xstore_branding_settings['control_panel']['hide_theme_builders']) && $xstore_branding_settings['control_panel']['hide_theme_builders'] == 'on')
            $hide_theme_builders = true;
        $show_pages_parsed = array();
        foreach ($show_pages as $show_page) {
            if (isset($xstore_branding_settings['control_panel']['page_' . $show_page]))
                $show_pages_parsed[] = $show_page;
        };
        $show_pages = $show_pages_parsed;
    }
    if ( isset($xstore_branding_settings['plugins_data'] ) ) {
        if (isset($xstore_branding_settings['plugins_data']['label']) && !empty($xstore_branding_settings['plugins_data']['label']))
            $custom_plugins_label = $xstore_branding_settings['plugins_data']['label'];
    }
}

$system = new Etheme_System_Requirements();
$system->system_test();
$result = $system->result();

$new_label = '<span style="margin-left: 5px; background: var(--et_admin_green-color, #489c33); letter-spacing: 1px; font-weight: 400; display: inline-block; border-radius: 3px; color: #fff; padding: 3px 2px 2px 3px; text-transform: uppercase; font-size: 8px; line-height: 1;">'.esc_html__('new', 'xstore').'</span>';
$hot_label = '<span style="margin-left: 5px; background: var(--et_admin_main-color, #A4004F); letter-spacing: 1px; font-weight: 400; display: inline-block; border-radius: 3px; color: #fff; padding: 3px 2px 2px 3px; text-transform: uppercase; font-size: 8px; line-height: 1;">'.esc_html__('hot', 'xstore').'</span>';
$beta_label = '<span style="margin-left: 5px; background: var(--et_admin_orange-color, #f57f17); letter-spacing: 1px; font-weight: 400; display: inline-block; border-radius: 3px; color: #fff; padding: 3px 2px 2px 3px; text-transform: uppercase; font-size: 8px; line-height: 1;">'.esc_html__('beta', 'xstore').'</span>';

$info_label = '<span class="dashicons dashicons-warning" style="color: var(--et_admin_orange-color);"></span>';
//$info_label = '';

$locked_icon = !$theme_active || !$core_active ? '<span class="dashicons dashicons-lock" style="width: 1rem;height: 1rem;font-size: 1rem;"></span>' : '';

$changelog_icon = '';
$welcome_icon = '';
$check_update = new ETheme_Version_Check();

$categories = array(
    'main' => array(
        'title' => esc_html__('Main', 'xstore'),
        'title_postfix' => false,
        'href' => admin_url( 'admin.php?page=et-panel-welcome' ),
        'active_item' => false,
        'items' => array()
    ),
    'content' => array(
        'title' => esc_html__('Content Management', 'xstore'),
        'title_postfix' => false,
        'href' => admin_url( 'admin.php?page=et-panel-welcome' ),
        'active_item' => false,
        'items' => array()
    ),
    'performance' => array(
        'title' => esc_html__('Performance & Optimization', 'xstore'),
        'title_postfix' => false,
        'href' => admin_url( 'admin.php?page=et-panel-welcome' ),
        'active_item' => false,
        'items' => array()
    ),
    'api' => array(
        'title' => esc_html__('API & Interaction', 'xstore'),
        'title_postfix' => false,
        'href' => admin_url( 'admin.php?page=et-panel-welcome' ),
        'active_item' => false,
        'items' => array()
    ),
    'maintenance' => array(
        'title' => esc_html__('Maintenance & Help', 'xstore'),
        'title_postfix' => false,
        'href' => admin_url( 'admin.php?page=et-panel-welcome' ),
        'active_item' => false,
        'items' => array()
    ),
    'customization' => array(
        'title' => esc_html__('Branding Customization', 'xstore'),
        'title_postfix' => false,
        'href' => admin_url( 'admin.php?page=et-panel-welcome' ),
        'active_item' => false,
        'items' => array()
    ),
);

if( $check_update->is_update_available() )
    $changelog_icon = '
    <span style="       
            display: inline-block;
            position: relative;
            min-width: 12px;
            height: 12px;
            margin: 0px 0px -2px 8px;
            background: #fff;">
        <span
            style="
                width: auto;
                height: auto;
                vertical-align: middle;
                position: absolute;
                left: -8px;
                top: -5px;
                font-size: 22px;"
            class="dashicons dashicons-warning dashicons-warning et_admin_bullet-green-color"></span>
    </span>';
$is_update_support = 'active'; //$check_update->get_support_status();
if( $is_update_support !='active' ) {
    if ( $is_update_support == 'expire-soon' ) {
        $welcome_icon = '
            <span style="       
                    display: inline-block;
                    position: relative;
                    min-width: 12px;
                    height: 12px;
                    margin: 0px 0px -2px 8px;
                    color: var(--et_admin_orange-color);
                    background: #fff;">
                <span
                    style="
                        width: auto;
                        height: auto;
                        vertical-align: middle;
                        position: absolute;
                        left: -8px;
                        top: -5px;
                        font-size: 22px;"
                    class="dashicons dashicons-warning dashicons-warning et_admin_bullet-orange-color"></span>
            </span>';
    } else {
        $welcome_icon = '
            <span style="       
                    display: inline-block;
                    position: relative;
                    min-width: 12px;
                    height: 12px;
                    margin: 0px 0px -2px 8px;
                    color: var(--et_admin_red-color);
                    background: #fff;">
                <span
                    style="
                        width: auto;
                        height: auto;
                        vertical-align: middle;
                        position: absolute;
                        left: -8px;
                        top: -5px;
                        font-size: 22px;"
                    class="dashicons dashicons-warning dashicons-warning et_admin_bullet-red-color"></span>
            </span>';
    }
}

$category = 'main';
if ( in_array('welcome', $show_pages) ) {
    $is_active = ( ! isset( $_GET['page'] ) || $_GET['page'] == 'et-panel-welcome' );
    $categories[$category]['items'][] = sprintf(
        '<li><a href="%s" class="%s">%s %s '.$welcome_icon.'</a></li>',
        admin_url( 'admin.php?page=et-panel-welcome' ),
        $is_active ? ' active' : '',
        '<span class="et-panel-nav-icon et-panel-nav-welcome"></span>',
        esc_html__( 'Welcome', 'xstore' )
    );
    if ( $is_active )
        $categories[$category]['active_item'] = $is_active;
}

if ( in_array('system_requirements', $show_pages) ) {
    $system_requirements_active = ( $_GET['page'] == 'et-panel-system-requirements' );
    $system_requirements = sprintf(
        '<li><a href="%s" class="%s">%s %s</a></li>',
        admin_url( 'admin.php?page=et-panel-system-requirements' ),
        $system_requirements_active ? ' active' : '',
        '<span class="et-panel-nav-icon et-panel-nav-system-status"></span>',
        esc_html__( 'System Status', 'xstore' ) . ( ( ! $result && $theme_active ) ? $info_label : '' )
    );
}
if ( ! $theme_active || ! $core_active ) {
    $categories[$category]['items'][] = $system_requirements;
    if ( $system_requirements_active )
        $categories[$category]['active_item'] = $system_requirements_active;
    if ( ! $result ) {
        $categories[$category]['title_postfix'] = $info_label;
    }
}

if ( in_array('customize', $show_pages) ) {
    $categories[$category]['items'][] = sprintf(
        ( ! $kirki_exists ) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
        ( ! $kirki_exists ) ? admin_url( 'themes.php?page=install-required-plugins&plugin_status=all' ) : wp_customize_url(),
        '',
        '<span class="et-panel-nav-icon et-panel-nav-theme-options"></span>',
        esc_html__( 'Theme Options', 'xstore' ) . (!$kirki_exists ? $locked_icon : '')
    );
}

if ( $is_elementor && !$hide_theme_builders ) {
    $categories[$category]['items'][] = sprintf(
        ((!$core_active || !$theme_active) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>'),
        (!$core_active || !$theme_active) ? admin_url('admin.php?page=et-panel-welcome') : admin_url( 'admin.php?page=et-panel-theme-builders' ),
        '',
        '<span class="et-panel-nav-icon et-panel-nav-xstore-builders"></span>',
        sprintf(esc_html__( '%s Builders', 'xstore' ), $branding_label) . ((!$core_active || !$theme_active) ? $locked_icon : '')
    );
}

if ( in_array('demos', $show_pages) ) {
    $is_active = ($_GET['page'] == 'et-panel-demos');
    $categories[$category]['items'][] = sprintf(
        (! $theme_active ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>'),
        admin_url('admin.php?page=et-panel-demos'),
        $is_active ? ' active' : '',
        '<span class="et-panel-nav-icon et-panel-nav-import-demos"></span>',
        esc_html__('Import Demos 130+', 'xstore') . (! $theme_active ? $locked_icon : '')
    );
    if ($is_active)
        $categories[$category]['active_item'] = $is_active;
}

// @todo change links and page conditions for this item then show it
//if ( in_array('demos', $show_pages) ) {
//    $is_active = ($_GET['page'] == 'et-panel-demos');
//    $categories[$category]['items'][] = sprintf(
//        (! $theme_active ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>'),
//        admin_url('admin.php?page=et-panel-demos'),
//        $is_active ? ' active' : '',
//        '<span class="et-panel-nav-icon et-panel-nav-import-demos"></span>',
//        esc_html__('Additional Pages', 'xstore') . (! $theme_active ? $locked_icon : '')
//    );
//    if ($is_active)
//        $categories[$category]['active_item'] = $is_active;
//}

if ( class_exists('WooCommerce') ) {

    if (in_array('sales_booster', $show_pages)) {
        $is_active = ( $_GET['page'] == 'et-panel-sales-booster' );
        $categories[$category]['items'][] = sprintf(
            (!$theme_active || !$core_active) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
            (!$theme_active || !$core_active) ? admin_url('admin.php?page=et-panel-welcome') : admin_url('admin.php?page=et-panel-sales-booster'),
            $is_active ? ' active' : '',
            '<span class="et-panel-nav-icon et-panel-nav-sales-booster"></span>',
//              '🚀&nbsp;&nbsp;' . esc_html__( 'Sales Booster', 'xstore' ) . $new_label
            esc_html__('Sales Booster', 'xstore') . $hot_label . ((!$core_active || !$theme_active) ? $locked_icon : '')
        );
        if ( $is_active )
            $categories[$category]['active_item'] = $is_active;
    }

    if (in_array('email_builder', $show_pages)) {
        $is_active = ( $_GET['page'] == 'et-panel-email-builder' );
        $categories[$category]['items'][] = sprintf(
            (!$core_active || !$theme_active) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
            ($theme_active && $core_active) ? admin_url('admin.php?page=et-panel-email-builder') : admin_url('admin.php?page=et-panel-welcome'),
            $is_active ? ' active' : '',
            '<span class="et-panel-nav-icon et-panel-nav-email-builder"></span>',
            esc_html__( 'Built-in Email Builder', 'xstore' ) . ((!$core_active || !$theme_active) ? $locked_icon : '')
        );
        if ( $is_active )
            $categories[$category]['active_item'] = $is_active;
    }
}

if ( in_array('plugins', $show_pages) ) {
    $is_active = ( $_GET['page'] == 'et-panel-plugins' );
    $categories[$category]['items'][] = sprintf(
        ( ! $theme_active ) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
        ( $theme_active ) ? admin_url( 'admin.php?page=et-panel-plugins' ) : admin_url( 'admin.php?page=et-panel-welcome' ),
        $is_active ? ' active' : '',
        '<span class="et-panel-nav-icon et-panel-nav-plugins-installer"></span>',
        esc_html__( 'Plugins Installer', 'xstore' ) . (!$theme_active ? $locked_icon : '')
    );
    if ( $is_active )
        $categories[$category]['active_item'] = $is_active;
}
if ( in_array('patcher', $show_pages) ) {
    $is_active = ( $_GET['page'] == 'et-panel-patcher' );
    $available_patches_affix = '';
    if ( class_exists('Etheme_Patcher') && $theme_active ) {
        $patcher = Etheme_Patcher::get_instance();
        $available_patches = count($patcher->get_available_patches(ETHEME_THEME_VERSION));
        if ( $available_patches ) {
            $available_patches_affix = ' <span class="et-title-label">'.
                $available_patches.
                '</span>';
            $categories[$category]['title_postfix'] = $available_patches_affix;
        }
    }
    $categories[$category]['items'][] = sprintf(
        ( ! $theme_active ) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
        ( $theme_active ) ? admin_url( 'admin.php?page=et-panel-patcher' ) : admin_url( 'admin.php?page=et-panel-welcome' ),
        $is_active ? ' active' : '',
        '<span class="et-panel-nav-icon et-panel-nav-patcher"></span>',
        esc_html__( 'Patcher', 'xstore' ) . $available_patches_affix . (!$theme_active ? $locked_icon : '')
    );
    if ( $is_active )
        $categories[$category]['active_item'] = $is_active;
}

$category = 'content';
if ( $theme_active ) {
    if (in_array('custom_fonts', $show_pages)) {
        $is_active = ($_GET['page'] == 'et-panel-custom-fonts');
        $categories[$category]['items'][] = sprintf(
            '<li><a href="%s" class="%s">%s %s</a></li>',
            admin_url('admin.php?page=et-panel-custom-fonts'),
            $is_active ? ' active' : '',
            '<span class="et-panel-nav-icon et-panel-nav-custom-fonts"></span>',
            esc_html__('Custom Fonts', 'xstore')
        );
        if ($is_active)
            $categories[$category]['active_item'] = $is_active;
    }
    if( get_theme_mod( 'etheme_slides', true ) && $is_elementor ) {
        $categories[$category]['items'][] = sprintf(
            ( ! $core_active ) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
            ( ! $core_active ) ? admin_url( 'admin.php?page=et-panel-welcome' ) : admin_url( 'edit.php?post_type=etheme_slides' ),
            '',
            '<span class="et-panel-nav-icon et-panel-nav-import-demos"></span>',
            esc_html__('Slides', 'xstore')
        );
    }
    if( get_theme_mod( 'static_blocks', true ) ) {
        $categories[$category]['items'][] = sprintf(
            ( ! $core_active ) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
            ( ! $core_active ) ? admin_url( 'admin.php?page=et-panel-welcome' ) : admin_url( 'edit.php?post_type=staticblocks' ),
            '',
            '<span class="et-panel-nav-icon et-panel-nav-static-blocks"></span>',
            esc_html__('Static Blocks', 'xstore')
        );
    }
}

if ( $theme_active && $core_active ) {
    $categories[$category]['items'][] = sprintf(
        '<li><a href="%s" class="%s">%s %s</a></li>',
        admin_url('widgets.php'),
        '',
        '<span class="et-panel-nav-icon et-panel-nav-widgets"></span>',
        esc_html__('Widgets', 'xstore')
    );
}

if ( $theme_active ) {
    if( get_theme_mod( 'portfolio_projects', true ) ) {
        $categories[$category]['items'][] = sprintf(
            ( ! $core_active ) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
            ( ! $core_active ) ? admin_url( 'admin.php?page=et-panel-welcome' ) : admin_url( 'edit.php?post_type=etheme_portfolio' ),
            '',
            '<span class="et-panel-nav-icon et-panel-nav-portfolio"></span>',
            esc_html__('Portfolio', 'xstore')
        );
    }
}

$category = 'performance';
if ( in_array('customize', $show_pages) ) {
    $categories[$category]['items'][] = sprintf(
        ( ! $kirki_exists ) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
        ( ! $kirki_exists ) ? admin_url( 'themes.php?page=install-required-plugins&plugin_status=all' ) : admin_url('/customize.php?autofocus[section]=general-optimization'),
        '',
        '<span class="et-panel-nav-icon et-panel-nav-speed-optimization"></span>',
        esc_html__( 'Speed Optimization', 'xstore' ) . (!$kirki_exists ? $locked_icon : '')
    );
}

$amp_tips = $mtips_notify;
if ( !$amp_active ) {
    $amp_url = admin_url( 'admin.php?page=et-panel-plugins&plugin=xstore-amp' );
    if ( $theme_active && $core_active )
        $amp_tips = sprintf(esc_html__( 'Install and Activate %s AMP plugin to use amp settings', 'xstore' ), $custom_plugins_label);
} else {
    $amp_url = admin_url('admin.php?page=et-panel-xstore-amp');
}

$is_active = ( $_GET['page'] == 'et-panel-xstore-amp' );
$categories[$category]['items'][] = sprintf(
    (!$core_active || !$theme_active || !$amp_active ) ? '<li class="mtips'.((!$core_active || !$theme_active) ? ' inactive' : '').'"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $amp_tips . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
    ( $theme_active && $core_active ) ? $amp_url : admin_url( 'admin.php?page=et-panel-welcome' ),
    $is_active ? ' active' : '',
    '<span class="et-panel-nav-icon et-panel-nav-amp-xstore"></span>',
    sprintf(esc_html__( 'AMP %s', 'xstore' ), $custom_plugins_label) . $locked_icon
);
if ($is_active)
    $categories[$category]['active_item'] = $is_active;

// api category
$category = 'api';
if ( in_array('open_ai', $show_pages) ) {
    $is_active = ($_GET['page'] == 'et-panel-open-ai');
    $categories[$category]['items'][] = sprintf(
        (!$core_active || !$theme_active) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
        ($theme_active && $core_active) ? admin_url('admin.php?page=et-panel-open-ai') : admin_url('admin.php?page=et-panel-welcome'),
        $is_active ? ' active' : '',
        '<span class="et-panel-nav-icon et-panel-nav-open-ai"></span>',
        esc_html__('ChatGPT (OpenAI)', 'xstore') . $locked_icon
    );
    if ($is_active)
        $categories[$category]['active_item'] = $is_active;
}

if ( in_array('social_authentication', $show_pages) ) {
    $is_active = ( $_GET['page'] == 'et-panel-social-authentication' );
    $categories[$category]['items'][] = sprintf(
        ( ! $core_active || ! $theme_active ) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
        ( $theme_active && $core_active ) ? admin_url( 'admin.php?page=et-panel-social-authentication' ) : admin_url( 'admin.php?page=et-panel-welcome' ),
        $is_active ? ' active' : '',
        '<span class="et-panel-nav-icon et-panel-nav-social-authentication"></span>',
        esc_html__( 'Social Authentication', 'xstore' ) . $locked_icon
    );
    if ($is_active)
        $categories[$category]['active_item'] = $is_active;
}

if ( in_array('social', $show_pages) ) {
    $is_active = ( $_GET['page'] == 'et-panel-social' );
    $categories[$category]['items'][] = sprintf(
        ( ! $core_active || ! $theme_active ) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
        ( $theme_active && $core_active ) ? admin_url( 'admin.php?page=et-panel-social' ) : admin_url( 'admin.php?page=et-panel-welcome' ),
        $is_active ? ' active' : '',
        '<span class="et-panel-nav-icon et-panel-nav-api-integrations"></span>',
        esc_html__( 'API Integrations', 'xstore' ) . $locked_icon
    );
    if ($is_active)
        $categories[$category]['active_item'] = $is_active;
}

// maintenance category
$category = 'maintenance';
if ( in_array( 'maintenance_mode', $show_pages ) ) {
    $is_active = ( $_GET['page'] == 'et-panel-maintenance-mode' );
    $categories[$category]['items'][] = sprintf(
        ( ! $core_active || ! $theme_active ) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
        ( $theme_active && $core_active ) ? admin_url( 'admin.php?page=et-panel-maintenance-mode' ) : admin_url( 'admin.php?page=et-panel-welcome' ),
        $is_active ? ' active' : '',
        '<span class="et-panel-nav-icon et-panel-nav-maintenance-mode"></span>',
        esc_html__( 'Maintenance Mode', 'xstore' ) . $locked_icon
    );
    if ($is_active)
        $categories[$category]['active_item'] = $is_active;
}
if ( in_array('support', $show_pages) ) {
    $is_active = ( $_GET['page'] == 'et-panel-support' );
    $categories[$category]['items'][] = sprintf(
        ( ! $core_active || ! $theme_active ) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
        ( $theme_active && $core_active ) ? admin_url( 'admin.php?page=et-panel-support' ) : admin_url( 'admin.php?page=et-panel-welcome' ),
        $is_active ? ' active' : '',
        '<span class="et-panel-nav-icon et-panel-nav-tutorials-support"></span>',
        esc_html__( 'Tutorials & Support', 'xstore' ) . $locked_icon
    );
    if ($is_active)
        $categories[$category]['active_item'] = $is_active;
}
if ( $theme_active && $core_active && $system_requirements ) {
    $categories[$category]['items'][] = $system_requirements;
    if ( $system_requirements_active )
        $categories[$category]['active_item'] = $system_requirements_active;
    if ( ! $result ) {
        $categories[$category]['title_postfix'] = $info_label;
    }
}

if ( in_array('changelog', $show_pages) ) {
    $is_active = ( $_GET['page'] == 'et-panel-changelog' );
    $categories[$category]['items'][] = sprintf(
        ( ! $core_active || ! $theme_active ) ? '<li class="mtips inactive"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $mtips_notify . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
        admin_url( 'admin.php?page=et-panel-changelog' ),
        $is_active ? ' active' : '',
        '<span class="et-panel-nav-icon et-panel-nav-changelog"></span>',
        esc_html__( 'Changelog', 'xstore' ) . $changelog_icon . $locked_icon
    );
    if ($is_active)
        $categories[$category]['active_item'] = $is_active;
}

// based on plugins because it that page is shown for customer then he can install White Label Plugin too
if ( in_array('plugins', $show_pages) && !class_exists('XStore_White_Label_Branding') ) {
    $branding_tips = $mtips_notify;
    $is_active = false;
    $branding_url = admin_url( 'admin.php?page=et-panel-plugins&plugin=xstore-white-label-branding' );
    if ( $theme_active && $core_active )
        $branding_tips = sprintf(esc_html__( 'Install and Activate %s White Label plugin to use White Label Branding settings', 'xstore' ), $custom_plugins_label);
    $categories[$category]['items'][] = sprintf(
        (!$core_active || !$theme_active || !$amp_active ) ? '<li class="mtips'.((!$core_active || !$theme_active) ? ' inactive' : '').'"><a href="%s" class="%s">%s %s</a><span class="mt-mes">' . $branding_tips . '</span></li>' : '<li><a href="%s" class="%s">%s %s</a></li>',
        ( $theme_active && $core_active ) ? $branding_url : admin_url( 'admin.php?page=et-panel-welcome' ),
        $is_active ? ' active' : '',
        '<span class="et-panel-nav-icon et-panel-nav-white-label"></span>',
        esc_html__( 'White Label Branding', 'xstore' ) . $locked_icon
    );
}


// additional category for some items added with hook action
if ( has_action('etheme_last_dashboard_nav_item') ) {
    $category = 'customization';
    ob_start();
    do_action('etheme_last_dashboard_nav_item');
    $customization = ob_get_clean();
    foreach (explode('<li', $customization) as $customization_item) {
        if ( !$customization_item ) continue;
        $categories[$category]['items'][] = '<li' . ((!$core_active || !$theme_active) ? ' class="mtips inactive" ' . str_replace(array('</a>', '</li>'), array( $locked_icon.'</a>', '<span class="mt-mes">' . $mtips_notify . '</span>' . '</li>'), $customization_item) : $customization_item);
//        $categories[$category]['items'][] = '<li' . $customization_item;
        // $categories[$category]['items'][] = '<li'.$customization_item;
        if ( strpos('active', $customization) !== false )
            $categories[$category]['active_item'] = true;
    }
}

$combined_list = array();
foreach ($categories as $category_key => $category_details) {
    $combined_list_local = '';
    if ( !count($category_details['items']) ) continue;
    $category_details['active_item'] = true; // temporary to show all opened @todo
    $combined_list_items = '<ul style="'.(!$category_details['active_item'] ? ' display: none;' : '').'">';
    $combined_list_items .= implode('', $category_details['items']);
    $combined_list_items .= '</ul>';
//        $combined_list_local .= '<li><span class="dashicons dashicons-arrow-down-alt2"></span><span>'.$category_details['title'].'</span>';
    $combined_list_local .= sprintf(
        '<li><a href="%s" class="%s et-nav-category">%s %s</a> %s</li>',
        $category_details['href'],
        $category_details['active_item'] ? ' opened' : '',
        '<span class="dashicons dashicons-arrow-'.($category_details['active_item'] ? 'down' : 'right').'"></span>',
        '<span>'.$category_details['title'].'</span>' . $category_details['title_postfix'],
        $combined_list_items
    );

    $combined_list[] = $combined_list_local;
}

$nav_collapser = '<span class="etheme-page-nav-collapser"><span class="dashicons dashicons-arrow-left"></span></span>';
echo '<div class="etheme-page-nav"><ul>' . implode('', $combined_list) . '</ul>'.$nav_collapser.'</div>';
