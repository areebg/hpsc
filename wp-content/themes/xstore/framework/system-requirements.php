<?php if ( ! defined( 'ETHEME_FW' ) ) {
	exit( 'No direct script access allowed' );
}

// **********************************************************************//
// ! System Requirements
// **********************************************************************//

class Etheme_System_Requirements {
	
	// ! Requirements
	public $requirements = array(         // ! Defaults
        'api_connection' => true,       // ! true
		'php'             => '7.4',     // ! 7.0
		'wp'              => '5.8.2',     // ! 3.9
		'ssl_version'     => '1.0',     // ! 1.0
		'wp_uploads'      => true,     // ! true
		'memory_limit'    => '128M',     // ! 128M
		'time_limit'      => array(
			'min' => 120,                 // ! 30
			'req' => 180                 // ! 60
		),
		'max_input_vars'  => array(
			'min' => 1000,                 // ! 1000
			'req' => 2000                 // ! 2000
		),
		'upload_filesize' => '10M',     // ! 10M
		'filesystem'      => 'direct', // ! direct
		'wp_remote_get'   => true,     // ! true
		'f_get_contents'  => true,     // ! true
		'gzip'            => true,     // ! true
		'DOMDocument'     => true,      // ! true
	);
	
	// ! Let's think that all alright
	public $result = true;
	
	// ! Just leave it here
	function __construct() {
	}
	
	// ! Return requirements
	public function get_requirements() {
		return $this->requirements;
	}
	
	// ! Return icon class, set result
	public function check( $type ) {
		if ( $type ) {
			return 'yes';
		} else {
			$this->result = false;
			
			return 'warning';
		}
		
		return $type;
	}
	
	// ! Return result. Note call it only after "html or system_test" functions!
	public function result() {
        $cache = get_transient('etheme_system_requirements_test_result', false);
        if ( $cache )
            return $cache;
        set_transient('etheme_system_requirements_test_result', $this->result, WEEK_IN_SECONDS);
		return $this->result;
	}
	
	// ! Return system information
	public function get_system($force_check = false) {
		global $wp_version;
		if ( $force_check ) {
            delete_transient( 'etheme_system_information' );
            delete_transient( 'etheme_system_requirements_test_result' );
        }
		else {
            $etheme_system = get_transient('etheme_system_information', false);
            if ($etheme_system)
                return $etheme_system;
        }
		
		$f_get_contents = str_replace( ' ', '_', 'file get contents' );
		ob_start();
        etheme_api_connection_notice();
        $api_connection_error = ob_get_clean();
		$system         = array(
		    'api_connection' => empty($api_connection_error),
			'php'             => PHP_VERSION,
			'wp'              => $wp_version,
			'curl_version'    => ( function_exists( 'curl_version' ) ) ? curl_version() : false,
			'ssl_version'     => '',
			'wp_uploads'      => wp_get_upload_dir(),
			'upload_filesize' => ini_get( 'upload_max_filesize' ),
			'memory_limit'    => ini_get( 'memory_limit' ),
			'time_limit'      => ini_get( 'max_execution_time' ),
			'max_input_vars'  => ini_get( 'max_input_vars' ),
			'filesystem'      => get_filesystem_method(),
			'wp_remote_get'   => function_exists( 'wp_remote_get' ),
			'f_get_contents'  => function_exists( 'file_get_contents' ),
			'gzip'            => is_callable( 'gzopen' ),
			'DOMDocument'     => class_exists('DOMDocument'),
		);

		if ($system['memory_limit'] == -1) {
			$system['memory_limit'] = WP_MEMORY_LIMIT;
		}
		
		if ( isset( $system['curl_version']['ssl_version'] ) ) {
			$system['ssl_version'] = $system['curl_version']['ssl_version'];
			$system['ssl_version'] = preg_replace( '/[^.0-9]/', '', $system['ssl_version'] );
		} else if ( extension_loaded( 'openssl' ) && defined( 'OPENSSL_VERSION_NUMBER' ) ) {
			$system['ssl_version'] = $this->get_openssl_version_number( true );
		} else {
			$system['ssl_version'] = 'undefined';
		}

        set_transient('etheme_system_information', $system, WEEK_IN_SECONDS);

		return $system;
	}
	
	// ! test system
	public function system_test($force_check = false) {
	    if ( !$force_check ) {
            $cache = get_transient('etheme_system_requirements_test_result', false);
            if ($cache)
                return;
        }
		$system = $this->get_system($force_check);
        ( $system['api_connection'] === $this->requirements['api_connection'] ) ? $this->check( true ) : $this->check( false );
		( $system['filesystem'] === $this->requirements['filesystem'] ) ? $this->check( true ) : $this->check( false );
		( version_compare( $system['php'], $this->requirements['php'], '>=' ) ) ? $this->check( true ) : $this->check( false );
		( version_compare( $system['wp'], $this->requirements['wp'], '>=' ) ) ? $this->check( true ) : $this->check( false );
		( wp_convert_hr_to_bytes( $system['memory_limit'] ) >= wp_convert_hr_to_bytes( $this->requirements['memory_limit'] ) ) ? $this->check( true ) : $this->check( false );
		( $system['time_limit'] >= $this->requirements['time_limit']['min'] ) ? $this->check( true ) : $this->check( false );
		( $system['max_input_vars'] >= ( $this->requirements['max_input_vars']['min'] ) ) ? $this->check( true ) : $this->check( false );
		( wp_convert_hr_to_bytes( $system['upload_filesize'] ) >= wp_convert_hr_to_bytes( $this->requirements['upload_filesize'] ) ) ? $this->check( true ) : $this->check( false );
		( wp_is_writable( $system['wp_uploads']['basedir'] ) === $this->requirements['wp_uploads'] ) ? $this->check( true ) : $this->check( false );
		( version_compare( $system['ssl_version'], $this->requirements['ssl_version'], '>=' ) ) ? $this->check( true ) : $this->check( false );
		( $system['gzip'] == $this->requirements['gzip'] ) ? $this->check( true ) : $this->check( false );
		( $system['f_get_contents'] == $this->requirements['f_get_contents'] ) ? $this->check( true ) : $this->check( false );
		( $system['wp_remote_get'] == $this->requirements['wp_remote_get'] ) ? $this->check( true ) : $this->check( false );
	}

    public function system_logs() {
        $system = $this->get_system();
        $logs = array();
        if ( $system['api_connection'] === $this->requirements['api_connection'] ) {}
        else {
            $logs[] = array(
                'type' => 'warning',
                'message' => sprintf(esc_html__('API server connection: Requirement - %1s ---  System - %2s', 'xstore'), esc_html__('success', 'xstore'), esc_html__('error', 'xstore'))
            );
        }

        if ( $system['filesystem'] === $this->requirements['filesystem'] ) {}
        else {
            $logs[] = array(
                'type' => 'warning',
                'message' => sprintf(esc_html__('WP File System: Requirement - %1s ---  System - %2s', 'xstore'), $this->requirements['filesystem'], $system['filesystem'])
            );
        }

        if ( version_compare( $system['php'], $this->requirements['php'], '>=' ) ) {}
        else {
            $logs[] = array(
                'type' => 'error',
                'message' => sprintf(esc_html__('PHP version: Requirement - %1s ---  System - %2s', 'xstore'), $this->requirements['php'], $system['php'] . (version_compare( $system['php'], $this->requirements['php'], '==' ) ? '(min)' : ''))
            );
        }

        if ( version_compare( $system['wp'], $this->requirements['wp'], '>=' ) ) {}
        else {
            $logs[] = array(
                'type' => 'warning',
                'message' => sprintf(esc_html__('WordPress version: Requirement - %1s ---  System - %2s', 'xstore'), $this->requirements['wp'], $system['wp'] . (version_compare( $system['wp'], $this->requirements['wp'], '==' ) ? '(min)' : ''))
            );
        }

        if ( wp_convert_hr_to_bytes( $system['memory_limit'] ) >= wp_convert_hr_to_bytes( $this->requirements['memory_limit'] ) ) {}
        else {
            $logs[] = array(
                'type' => 'warning',
                'message' => sprintf(esc_html__('Memory limit: Requirement - %1s ---  System - %2s', 'xstore'), wp_convert_hr_to_bytes($this->requirements['memory_limit']), wp_convert_hr_to_bytes($system['memory_limit']) . ( wp_convert_hr_to_bytes( $system['memory_limit'] ) === wp_convert_hr_to_bytes( $this->requirements['memory_limit'] ) ? '(min)' : ''))
            );
        }

        if ( $system['time_limit'] >= $this->requirements['time_limit']['req'] ) {}
        else {
            $logs[] = array(
                'type' => ($system['time_limit'] < $this->requirements['time_limit']['min'] ? 'error' : 'warning'),
                'message' => sprintf(esc_html__('Max execution time: Requirement - %1s ---  System - %2s', 'xstore'), $this->requirements['time_limit']['min'], $system['time_limit'] . ( (int) $system['time_limit'] === (int) $this->requirements['time_limit']['min'] ? '(min)' : ''))
            );
        }

        if ( $system['max_input_vars'] >= $this->requirements['max_input_vars']['req'] ) {}
        else {
            $logs[] = array(
                'type' => ($system['max_input_vars'] < $this->requirements['max_input_vars']['min'] ? 'error' : 'warning'),
                'message' => sprintf(esc_html__('Max input vars: Requirement - %1s ---  System - %2s', 'xstore'), $this->requirements['max_input_vars']['req'], $system['max_input_vars'] . ( (int) $system['max_input_vars'] === (int) $this->requirements['max_input_vars']['min'] ? '(min)' : ''))
            );
        }

        if ( wp_convert_hr_to_bytes( $system['upload_filesize'] ) >= wp_convert_hr_to_bytes( $this->requirements['upload_filesize'] ) ) {}
        else {
            $logs[] = array(
                'type' => 'warning',
                'message' => sprintf(esc_html__('Upload max filesize: Requirement - %1s ---  System - %2s', 'xstore'), $this->requirements['upload_filesize'], $system['upload_filesize'] . ( (int) wp_convert_hr_to_bytes( $system['upload_filesize'] ) === (int) wp_convert_hr_to_bytes( $this->requirements['upload_filesize'] ) ? '(min)' : ''))
            );
        }

        if ( wp_is_writable( $system['wp_uploads']['basedir'] ) === $this->requirements['wp_uploads'] ) {}
        else {
            $logs[] = array(
                'type' => 'warning',
                'message' => sprintf(esc_html__('../Uploads folder: Requirement - %1s ---  System - %2s', 'xstore'), 'writable', 'unwritable')
            );
        }

        if ( version_compare( $system['ssl_version'], $this->requirements['ssl_version'], '>=' ) ) {}
        else {
            $logs[] = array(
                'type' => 'warning',
                'message' => sprintf(esc_html__('OpenSSL version: Requirement - %1s ---  System - %2s', 'xstore'), $this->requirements['ssl_version'], $system['ssl_version'] . ( version_compare( $system['ssl_version'], $this->requirements['ssl_version'], '==' ) ? '(min)' : ''))
            );
        }

        if ( $system['gzip'] == $this->requirements['gzip'] ) {}
        else {
            $logs[] = array(
                'type' => 'warning',
                'message' => sprintf(esc_html__('GZIP compression: Requirement - %1s ---  System - %2s', 'xstore'), 'enable', 'disable')
            );
        }

        if ( $system['f_get_contents'] == $this->requirements['f_get_contents'] ) {}
        else {
            $logs[] = array(
                'type' => 'warning',
                'message' => sprintf(esc_html__('%1s: Requirement - %2s ---  System - %3s', 'xstore'), str_replace(' ', '_', 'file get contents') . '( )', 'enable', 'disable')
            );
        }

        if ( $system['wp_remote_get'] == $this->requirements['wp_remote_get'] ) {}
        else {
            $logs[] = array(
                'type' => 'warning',
                'message' => sprintf(esc_html__('wp_remote_get( ): Requirement - %1s ---  System - %2s', 'xstore'), 'enable', 'disable')
            );
        }

        return $logs;
    }
	
	// ! Show html result
	public function html() {
		$system = $this->get_system();
        $helper = '<span class="mtips"><span class="dashicons dashicons-editor-help"></span><span class="mt-mes">%s</span></span>';
		
		echo '<table class="system-requirements">';
		printf(
			'<thead><th class="requirement-headings environment">%1$s</th>
				<th>%2$s</th>
				<th>%3$s</th></thead>',
			esc_html__( 'Environment', 'xstore' ),
			esc_html__( 'Requirement', 'xstore' ),
			esc_html__( 'System', 'xstore' )
		);

        printf(
            '<tr class="api-connection %3$s">
					<td>' . esc_html__( 'API server connection:', 'xstore' ) . '</td>
					<td>%1$s</td>
					<td>%2$s %4$s</td>
				</tr>',
            esc_html__('success', 'xstore'),
            ( $system['api_connection'] === $this->requirements['api_connection'] ) ? esc_html__('success', 'xstore') : esc_html__('error', 'xstore'),
            ( $system['api_connection'] === $this->requirements['api_connection'] ) ? $this->check( true ) : $this->check( false ),
            ( $system['api_connection'] === $this->requirements['api_connection'] ) ?
                '<span class="dashicons dashicons-'.$this->check( true ).'"></span>' :
                ('<span class="mtips mtips-lg mtips-left"><span class="dashicons dashicons-'.$this->check( false ).'"></span><span class="mt-mes">'.
                    esc_html__('We are unable to connect to the XStore API with the XStore theme. Please check your SSL certificate or white lists.', 'xstore').
                '</span>')
        );

		printf(
			'<tr class="wp-system %3$s">
					<td>' . esc_html__( 'WP File System:', 'xstore' ) . '</td>
					<td>%1$s</td>
					<td>%2$s<span class="dashicons dashicons-%3$s "></span></td>
				</tr>',
			$this->requirements['filesystem'],
			$system['filesystem'],
			( $system['filesystem'] === $this->requirements['filesystem'] ) ? $this->check( true ) : $this->check( false )
		);
		
		printf(
			'<tr class="php-version %3$s">
					<td>' . esc_html__( 'PHP version:', 'xstore' ) . '</td>
					<td>%1$s</td>
					<td>%2$s<span class="dashicons dashicons-%3$s %4$s"></span></td>
				</tr>',
			$this->requirements['php'],
			$system['php'],
			( version_compare( $system['php'], $this->requirements['php'], '>=' ) ) ? $this->check( true ) : $this->check( false ),
			( version_compare( $system['php'], $this->requirements['php'], '==' ) ) ? 'min' : ''
		);
		
		printf(
			'<tr class="wp-version %3$s">
					<td>' . esc_html__( 'WordPress version:', 'xstore' ) . '</td>
					<td>%1$s</td>
					<td>%2$s<span class="dashicons dashicons-%3$s %4$s"></span></td>
				</tr>',
			$this->requirements['wp'],
			$system['wp'],
			( version_compare( $system['wp'], $this->requirements['wp'], '>=' ) ) ? $this->check( true ) : $this->check( false ),
			( version_compare( $system['wp'], $this->requirements['wp'], '==' ) ) ? 'min' : ''
		);
		
		printf(
			'<tr class="memory-limit %3$s">
					<td>' . esc_html__( 'Memory limit:', 'xstore' ) . '</td>
					<td>%1$s</td>
					<td>%2$s<span class="dashicons dashicons-%3$s %4$s"></span></td>
				</tr>',
			$this->requirements['memory_limit'],
			$system['memory_limit'],
			( wp_convert_hr_to_bytes( $system['memory_limit'] ) >= wp_convert_hr_to_bytes( $this->requirements['memory_limit'] ) ) ? $this->check( true ) : $this->check( false ),
			( wp_convert_hr_to_bytes( $system['memory_limit'] ) === wp_convert_hr_to_bytes( $this->requirements['memory_limit'] ) ) ? 'min' : ''
		);
		
		printf(
			'<tr class="execution-time %1$s %2$s">
					<td>' . esc_html__( 'Max execution time:', 'xstore' ) . '</td>
					<td>min (%3$s-%4$s)</td>
					<td>%5$s<span class="dashicons dashicons-%6$s %7$s"></td>
				</tr>',
			( $system['time_limit'] >= $this->requirements['time_limit']['req'] ) ? '' : 'warning',
			( (int) $system['time_limit'] === (int) $this->requirements['time_limit']['min'] ) ? 'min' : '',
			$this->requirements['time_limit']['min'],
			$this->requirements['time_limit']['req'],
			$system['time_limit'],
			( $system['time_limit'] >= $this->requirements['time_limit']['min'] ) ? $this->check( true ) : $this->check( false ),
			( $system['time_limit'] >= $this->requirements['time_limit']['req'] ) ? '' : 'dashicons-warning'
		);
		
		printf(
			'<tr class="input-vars %1$s %2$s">
					<td>' . esc_html__( 'Max input vars:', 'xstore' ) . '</td>
					<td>min (%3$s-%4$s)</td>
					<td>%5$s<span class="dashicons dashicons-%6$s %7$s"></span></td>
				</tr>',
			( $system['max_input_vars'] >= $this->requirements['max_input_vars']['req'] ) ? '' : 'warning',
			( (int) $system['max_input_vars'] === (int) $this->requirements['max_input_vars']['min'] ) ? 'min' : '',
			$this->requirements['max_input_vars']['min'],
			$this->requirements['max_input_vars']['req'],
			$system['max_input_vars'],
			( $system['max_input_vars'] >= ( $this->requirements['max_input_vars']['min'] ) ) ? $this->check( true ) : $this->check( false ),
			( $system['max_input_vars'] >= $this->requirements['max_input_vars']['req'] ) ? '' : 'dashicons-warning'
		);
		
		printf(
			'<tr class="filesize %3$s">
					<td>' . esc_html__( 'Upload max filesize:', 'xstore' ) . '</td>
					<td>%1$s</td>
					<td>%2$s<span class="dashicons dashicons-%3$s %4$s"></span></td>
				</tr>',
			$this->requirements['upload_filesize'],
			$system['upload_filesize'],
			( wp_convert_hr_to_bytes( $system['upload_filesize'] ) >= wp_convert_hr_to_bytes( $this->requirements['upload_filesize'] ) ) ? $this->check( true ) : $this->check( false ),
			( (int) wp_convert_hr_to_bytes( $system['upload_filesize'] ) === (int) wp_convert_hr_to_bytes( $this->requirements['upload_filesize'] ) ) ? 'min' : ''
		);

		printf(
			'<tr class="uploads-folder %3$s">
					<td>' . esc_html__( '../Uploads folder:', 'xstore' ) . '</td>
					<td>%1$s</td>
					<td>%2$s<span class="dashicons dashicons-%3$s "></span></td>
				</tr>',
			'writable',
			( wp_is_writable( $system['wp_uploads']['basedir'] ) === $this->requirements['wp_uploads'] ) ? 'writable' : 'unwritable',
			( wp_is_writable( $system['wp_uploads']['basedir'] ) === $this->requirements['wp_uploads'] ) ? $this->check( true ) : $this->check( false )
		);
		
		printf(
			'<tr class="ssl-version %3$s">
					<td>' . esc_html__( 'OpenSSL version:', 'xstore' ) . '</td>
					<td>%1$s</td>
					<td>%2$s<span class="dashicons dashicons-%3$s %4$s"></span></td>
				</tr>',
			$this->requirements['ssl_version'],
			$system['ssl_version'],
			( version_compare( $system['ssl_version'], $this->requirements['ssl_version'], '>=' ) ) ? $this->check( true ) : $this->check( false ),
			( version_compare( $system['ssl_version'], $this->requirements['ssl_version'], '==' ) ) ? 'min' : ''
		);
		
		printf(
			'<tr class="gzip-compression %3$s">
					<td>' . esc_html__( 'GZIP compression:', 'xstore' ) . '</td>
					<td>%1$s</td>
					<td>%2$s<span class="dashicons dashicons-%3$s "></span></td>
				</tr>',
			'enable',
			( $system['gzip'] == $this->requirements['gzip'] ) ? 'enable' : 'disable',
			( $system['gzip'] == $this->requirements['gzip'] ) ? $this->check( true ) : $this->check( false )
		);
		
		printf(
			'<tr class="function-f_get_contents %3$s">
					<td>' . str_replace( ' ', '_', 'file get contents' ) . '( ):</td>
					<td>%1$s</td>
					<td>%2$s<span class="dashicons dashicons-%3$s "></span></td>
				</tr>',
			( $this->requirements['f_get_contents'] ) ? 'enable' : 'disable',
			( $system['f_get_contents'] == $this->requirements['f_get_contents'] ) ? 'enable' : 'disable',
			( $system['f_get_contents'] == $this->requirements['f_get_contents'] ) ? $this->check( true ) : $this->check( false )
		);
		
		printf(
			'<tr class="function-wp_remote_get %3$s">
					<td>wp_remote_get( ):</td>
					<td>%1$s</td>
					<td>%2$s<span class="dashicons dashicons-%3$s "></span></td>
				</tr>',
			( $this->requirements['wp_remote_get'] ) ? 'enable' : 'disable',
			( $system['wp_remote_get'] == $this->requirements['wp_remote_get'] ) ? 'enable' : 'disable',
			( $system['wp_remote_get'] == $this->requirements['wp_remote_get'] ) ? $this->check( true ) : $this->check( false )
		);

		printf(
			'<tr class="class-DOMDocument %3$s">
					<td>DOMDocument</td>
					<td>%1$s</td>
					<td>%2$s<span class="dashicons dashicons-%3$s "></span></td>
				</tr>',
			( $this->requirements['DOMDocument'] ) ? 'enable' : 'disable',
			( $system['DOMDocument'] == $this->requirements['DOMDocument'] ) ? 'enable' : 'disable',
			( $system['DOMDocument'] == $this->requirements['DOMDocument'] ) ? $this->check( true ) : $this->check( false )
		);
		echo '</table>';
	}

	public function wp_html() {
        $system = $this->get_system();
        echo '<table class="system-requirements">';

        $helper = '<span class="mtips"><span class="dashicons dashicons-editor-help"></span><span class="mt-mes">%s</span></span>';

        printf(
            '<tr class="wp-home-url">
					<td>%1$s</td>
					<td>%2$s</td>
				</tr>',
            esc_html__( 'Home url:', 'xstore' ) . sprintf($helper, esc_attr__( 'The URL of your site\'s homepage.', 'xstore' )),
             esc_url_raw(home_url())
        );

        printf(
            '<tr class="wp-site-url">
					<td>%1$s</td>
					<td>%2$s</td>
				</tr>',
            esc_html__( 'Site url:', 'xstore' ) . sprintf($helper, esc_attr__( 'The root URL of your site.', 'xstore' )),
            esc_url_raw(site_url())
        );

        printf(
            '<tr class="wp-content-path">
					<td>%1$s</td>
					<td>%2$s</td>
				</tr>',
            esc_html__( 'WP Content Path:', 'xstore' ) . sprintf($helper, esc_attr__( 'System path of your wp-content directory.', 'xstore' )),
            (defined( 'WP_CONTENT_DIR' ) ? esc_html( WP_CONTENT_DIR ) : esc_html__( 'N/A', 'xstore' ))
        );

        printf(
            '<tr class="wp-root-path">
					<td>%1$s</td>
					<td>%2$s</td>
				</tr>',
            esc_html__( 'WP Path:', 'xstore' ) . sprintf($helper, esc_attr__( 'System path of your WP root directory.', 'xstore' )),
            (defined( 'ABSPATH' ) ? esc_html( ABSPATH ) : esc_html__( 'N/A', 'xstore' ))
        );

        printf(
            '<tr class="wp-multisite">
					<td>%1$s</td>
					<td>%2$s</td>
				</tr>',
            esc_html__( 'WP Multisite:', 'xstore' ) . sprintf($helper, esc_attr__( 'Whether or not you have WordPress Multisite enabled.', 'xstore' )),
            (is_multisite() ? esc_html__('Yes', 'xstore') : esc_html__('No', 'xstore'))
        );

        printf(
            '<tr class="wp-debug-mode">
					<td>%1$s</td>
					<td>%2$s</td>
				</tr>',
            esc_html__( 'WP Debug Mode:', 'xstore' ) . sprintf($helper, esc_attr__( 'Displays whether or not WordPress is in Debug Mode.', 'xstore' )),
            ((defined( 'WP_DEBUG' ) && WP_DEBUG) ? esc_html__('Yes', 'xstore') : esc_html__('No', 'xstore'))
        );

        printf(
            '<tr class="wp-language">
					<td>%1$s</td>
					<td>%2$s</td>
				</tr>',
            esc_html__( 'Language:', 'xstore' ) . sprintf($helper, esc_attr__( 'The current language used by WordPress. Default = English.', 'xstore' )),
            esc_html( get_locale() )
        );

        printf(
            '<tr class="wp-theme-version">
					<td>%1$s</td>
					<td>%2$s</td>
				</tr>',
            esc_html__( 'Theme version:', 'xstore' ) . sprintf($helper, esc_attr__( 'The current version of theme used.', 'xstore' )),
            ETHEME_THEME_VERSION
        );

        if ( is_child_theme() ) {
            $theme   = wp_get_theme();
            $version = $theme->get( 'Version' );
            printf(
                '<tr class="wp-child-theme-version">
					<td>%1$s</td>
					<td>%2$s</td>
				</tr>',
                esc_html__('Child theme version:', 'xstore') . sprintf($helper, esc_attr__('The current version of child theme used.', 'xstore')),
                $version
            );
        }

        echo '</table>';
    }

    public function wp_active_plugins() {
        $active_plugins = (array) get_option( 'active_plugins', [] );

        if ( is_multisite() ) {
            $active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', [] ) ) );
        }

        if ( count($active_plugins) ) {
            ?>
            <h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__('Active plugins', 'xstore'); ?></h2>
            <br/>
            <?php
        }

        echo '<table class="system-requirements">';

        printf(
            '<thead><th class="requirement-headings environment">%1$s</th>
				<th>%2$s</th>
				<th>%3$s</th></thead>',
            esc_html__( 'Name', 'xstore' ),
            esc_html__( 'Version', 'xstore' ),
            esc_html__( 'Author', 'xstore' )
        );

        $helper = '<span class="mtips mtips-lg"><span class="dashicons dashicons-editor-help"></span><span class="mt-mes">%s</span></span>';


        foreach ( $active_plugins as $plugin_file ) {

            $plugin_data    = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file );

            if ( ! empty( $plugin_data['Name'] ) ) {

                // Link the plugin name to the plugin url if available.
                if ( ! empty( $plugin_data['PluginURI'] ) ) {
                    $plugin_name = '<a href="' . esc_url( $plugin_data['PluginURI'] ) . '" title="' . __( 'Visit plugin homepage', 'xstore' ) . '">' . esc_html( $plugin_data['Name'] ) . '</a>';
                } else {
                    $plugin_name = esc_html( $plugin_data['Name'] );
                }
                ?>
                <tr>
                    <td>
                        <?php echo $plugin_name . sprintf($helper, $plugin_data['Description']); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                    </td>
                    <td>
                        <?php echo ' v.'.$plugin_data['Version']; ?>
                    </td>
                    <td>
                        <?php $author_name = preg_replace( '#<a.*?>([^>]*)</a>#i', '$1', $plugin_data['AuthorName'] ); ?>
                        <?php echo'<a href="' . esc_url( $plugin_data['AuthorURI'] ) . '" target="_blank">' . esc_html( $author_name ) . '</a>'; ?>
                    </td>
                </tr>
                <?php
            }
        }

        echo '</table>';

        echo '<br/>';
        echo '<br/>';
    }
	
	public function get_openssl_version_number( $patch_as_number = false, $openssl_version_number = null ) {
		if ( is_null( $openssl_version_number ) ) {
			$openssl_version_number = OPENSSL_VERSION_NUMBER;
		}
		$openssl_numeric_identifier = str_pad( (string) dechex( $openssl_version_number ), 8, '0', STR_PAD_LEFT );
		
		$openssl_version_parsed = array();
		$preg                   = '/(?<major>[[:xdigit:]])(?<minor>[[:xdigit:]][[:xdigit:]])(?<fix>[[:xdigit:]][[:xdigit:]])';
		$preg                   .= '(?<patch>[[:xdigit:]][[:xdigit:]])(?<type>[[:xdigit:]])/';
		preg_match_all( $preg, $openssl_numeric_identifier, $openssl_version_parsed );
		$openssl_version = false;
		if ( ! empty( $openssl_version_parsed ) ) {
			$alphabet        = array(
				1  => 'a',
				2  => 'b',
				3  => 'c',
				4  => 'd',
				5  => 'e',
				6  => 'f',
				7  => 'g',
				8  => 'h',
				9  => 'i',
				10 => 'j',
				11 => 'k',
				12 => 'l',
				13 => 'm',
				14 => 'n',
				15 => 'o',
				16 => 'p',
				17 => 'q',
				18 => 'r',
				19 => 's',
				20 => 't',
				21 => 'u',
				22 => 'v',
				23 => 'w',
				24 => 'x',
				25 => 'y',
				26 => 'z'
			);
			$openssl_version = intval( $openssl_version_parsed['major'][0] ) . '.';
			$openssl_version .= intval( $openssl_version_parsed['minor'][0] ) . '.';
			$openssl_version .= intval( $openssl_version_parsed['fix'][0] );
			$patchlevel_dec  = hexdec( $openssl_version_parsed['patch'][0] );
			if ( ! $patch_as_number && array_key_exists( $patchlevel_dec, $alphabet ) ) {
				$openssl_version .= $alphabet[ $patchlevel_dec ]; // ideal for text comparison
			} else {
				$openssl_version .= '.' . $patchlevel_dec; // ideal for version_compare
			}
		}
		
		return $openssl_version;
	}
}