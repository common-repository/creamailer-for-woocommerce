<?php
if ( ! class_exists( 'Creamailer' ) ) {
	require_once dirname( __FILE__ ) . '/class-creamailer.php';
}
if ( ! class_exists( 'CfwLogger' ) ) {
	require_once dirname( __FILE__ ) . '/class-cfwlogger.php';
}

class CreamailerForWordpressPlugin {
	protected $settings;
	protected $lists; protected $pluginurl;
	protected $connected; /* do not trust this */

	public function __construct() {
		$this->settings = get_option( 'creamailer-for-woocommerce' );
		$this->pluginurl = CREAMAILER_FOR_WOOCOMMERCE_PLUGIN_URL;
		$this->connected = false;

		add_action( 'admin_init', array( $this, 'admin_init_hook' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu_hook' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'woocommerce_checkout_order_processed_hook' ), 10, 3 );
		add_filter( 'woocommerce_checkout_fields', array( $this, 'woocommerce_checkout_fields_filter' ) );
	}

	public function woocommerce_checkout_fields_filter($fields) {
		if ( ! $this->settings['hide_consent'] ) {
			$fields['order']['marketing_email'] = array(
			'label'    => esc_html($this->settings['optin_text']),
			'type'     => 'checkbox',
			'required' => false,
			'visible'  => true,
			);
		}
		return $fields;
	}

	public function woocommerce_checkout_order_processed_hook( $order_id, $posted_data, $order ) {
		if ( ( ! $this->settings['hide_consent'] ) && ( ! $posted_data['marketing_email'] ) ) {
			return;
		}
		$email      = $posted_data['billing_email'];
		$name       = $posted_data['billing_first_name'] . ' ' . $posted_data['billing_last_name'];
		$creamailer = new Creamailer(
			$this->settings['access_token'],
			$this->settings['shared_secret'],
			$this->settings['list_id'],
		);
		$creamailer->add_subscriber( $email, $name, $this->settings['send_autoresponders'], $this->settings['send_autoresponders_if_exists'] );
	}

	public function admin_menu_hook() {
		load_plugin_textdomain( 'creamailer-for-woocommerce', false, plugin_basename( dirname( __FILE__ ) ) . '/../i18n/languages' );
		add_options_page(
			'Creamailer Plugin Settings',
			'Creamailer WooCommerce',
			'manage_options',
			'creamailer-for-woocommerce',
			array( $this, 'render_settings_page' )
		);
		wp_enqueue_style( 'cfw-style', $this->pluginurl . '/assets/src/css/cfw-style.css', array(), CREAMAILER_FOR_WOOCOMMERCE_VERSION );
		wp_enqueue_script( 'cfw-js', $this->pluginurl . '/assets/src/js/cfw-js.js', array(), CREAMAILER_FOR_WOOCOMMERCE_VERSION );
	}

	public function admin_init_hook() {
		register_setting(
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce'
		);

		add_settings_section(
			'creamailer-for-woocommerce',
			'',
			array( $this, 'settings_section_html' ),
			'creamailer-for-woocommerce'
		);

		add_settings_field(
			'access_token',
			'',
			array( $this, 'print_access_token_field' ),
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce'
		);

		add_settings_field(
			'shared_secret',
			'',
			array( $this, 'print_shared_secret_field' ),
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce'
		);

		add_settings_field(
			'list_id',
			'',
			array( $this, 'print_list_id_field' ),
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce'
		);
		add_settings_field(
			'optin_text',
			'',
			array( $this, 'print_optin_text_field' ),
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce'
		);
		add_settings_field(
			'hide_consent',
			'',
			array( $this, 'print_consent_message_field' ),
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce'
		);
		add_settings_field(
			'send_autoresponders',
			'',
			array( $this, 'print_send_autoresponders_field' ),
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce'
		);
		add_settings_field(
			'send_autoresponders_if_exists',
			'',
			array( $this, 'print_send_autoresponders_if_exists_field' ),
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce'
		);
		add_settings_field(
			'error_log',
			'',
			array( $this, 'print_error_log_field' ),
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce',
			'creamailer-for-woocommerce'
		);
	}

	protected function update_lists() {
		$creamailer = new Creamailer(
			$this->settings['access_token'],
			$this->settings['shared_secret'],
			0,
			$this->settings['error_log']
		);

		$res = $creamailer->get_lists();

		if ( ! is_array( $res ) ) {
			$this->lists = null;
			return;
		}
		$this->lists = array();
		foreach ( $res as $i ) {
			array_push( $this->lists, $i );
		}
		/* XXX  probably wrong place to set this */
		$this->connected = true;
	}

	public function settings_section_html() {
		printf( '<h1>' . esc_html__( 'Creamailer integration for WooCommerce', 'creamailer-for-woocommerce' ) . '</h1>' );
		printf( '<div id="cfw-settings-header">' );
		printf( '<img src="%s/assets/images/logo.png" alt="Creamailer" />', $this->pluginurl );
		printf( '<sup>Version %s</sup>', CREAMAILER_FOR_WOOCOMMERCE_VERSION );
		printf( '</div>' );
		printf( '<hr>' );
		printf( '<div id="cfw-plugin-api-container">' );
		printf( '<h2>' . esc_html__( 'API-Settings', 'creamailer-for-woocommerce' ) . '</h2>' );
		if ( $this->connected ) {
			printf( '<small id="cfw-plugin-connected" class="cfw-plugin-connect-indicator">' . esc_html__( 'connected', 'creamailer-for-woocommerce' ) . '</small>' );
		} else {
			printf( '<small id="cfw-plugin-not-connected" class="cfw-plugin-connect-indicator">' . esc_html__( 'No connection', 'creamailer-for-woocommerce' ) . '</small>' );
		}
		printf( ' </div>' );
	}

	public function print_access_token_field() {
		printf( '<p class="cfw-required">' . esc_html__( 'Access token', 'creamailer-for-woocommerce' ) . '</p>' );
		printf( '<input id="cfw-plugin-access-token-field" type="text" name="creamailer-for-woocommerce[access_token]" value="%s" size="40" />', esc_html( $this->settings['access_token'] ) );
		printf( '<p>' . esc_html__( 'Your access token (asiakastunnus)', 'creamailer-for-woocommerce' ) . '</p>' );

	}

	public function print_shared_secret_field() {
		printf( '<p class="cfw-required">' . esc_html__( 'Shared secred', 'creamailer-for-woocommerce' ) . '</p>' );
		printf( '<input id="cfw-plugin-shared-secret-field" type="password" name="creamailer-for-woocommerce[shared_secret]" value="%s" size="40" />', esc_html( $this->settings['shared_secret'] ) );
		printf( '<p>' . esc_html__( 'Your shared secred (yhteinen tunniste)', 'creamailer-for-woocommerce' ) . '</p>' );
	}

	public function print_optin_text_field() {
		printf( '<p class="cfw-required">' . esc_html__( 'Opt-in text', 'creamailer-for-woocommerce' ) . '</p>' );
		printf(
			'<input id="cfw-plugin-optin-text-field" type="text" name="creamailer-for-woocommerce[optin_text]" value="%s" size="40" />',
			$this->settings['optin_text'] ?
			esc_html( $this->settings['optin_text'] ) :
			esc_html__( 'Subscribe to our newsletter', 'creamailer-for-woocommerce' )
		);
		printf( '<p>' . esc_html__( 'Opt-in text', 'creamailer-for-woocommerce' ) . '</p>' );

	}

	public function print_list_id_field() {
		if ( ! $this->lists ) {
			return;
		}

		printf( '<p class="cfw-required">' . esc_html__( 'Mailinglist', 'creamailer-for-woocommerce' ) . '</p>' );
		printf( '<select name="creamailer-for-woocommerce[list_id]" />' );
		foreach ( $this->lists as $i ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_html( $i['id'] ),
				esc_html( $this->settings['list_id'] === $i['id'] ? 'selected' : '' ),
				esc_html( $i['name'] )
			);
		}
		printf( '</select>' );
		printf( '<p>' . esc_html__( 'Choose mailinglist', 'creamailer-for-woocommerce' ) . '</p>' );
	}

	public function print_error_log_field() {
		printf(
			'<input id="cfw-plugin-error-log-field" type="checkbox" value="1" name="creamailer-for-woocommerce[error_log]" %s />',
			$this->settings['error_log'] ? 'checked="checked"' : ''
		);
		printf( '<span id="cfw-plugin-error-log-text">' . esc_html__( 'Enable error log', 'creamailer-for-woocommerce' ) . '</span>' );
	}

	public function print_consent_message_field() {
		printf( '<hr id="cfw-send-newsletters" />' );
		printf(
			'<input id="cfw-show-consent-field" type="checkbox" value="1" name="creamailer-for-woocommerce[hide_consent]" %s />',
			(isset($this->settings['hide_consent']) && $this->settings['hide_consent'] === '1') ? 'checked="checked"' : ''
		);
		printf( '<span id="cfw-show-consent-text">' . esc_html__( 'Hide the permission question and add to the list', 'creamailer-for-woocommerce' ) . '</span>' );
}

	public function print_send_autoresponders_field() {
		printf(
			'<input id="cfw-plugin-send-autoresponders-field" type="checkbox" value="1" name="creamailer-for-woocommerce[send_autoresponders]" %s />',
			$this->settings['send_autoresponders'] ? 'checked="checked"' : ''
		);
		printf( '<span id="cfw-plugin-send-autoresponders-text">' . esc_html__( 'Send mailinglists automatic messages', 'creamailer-for-woocommerce' ) . '</span>' );
	}

	public function print_send_autoresponders_if_exists_field() {
		printf(
			'<input id="cfw-plugin-send-autoresponders-if-exists-field" type="checkbox" value="1" name="creamailer-for-woocommerce[send_autoresponders_if_exists]" %s />',
			$this->settings['send_autoresponders_if_exists'] ? 'checked="checked"' : ''
		);
		printf( '<span id="cfw-plugin-send-autoresponders-if-exists-text">' . esc_html__( 'Send mailinglists automatic messages even if subscriber exists', 'creamailer-for-woocommerce' ) . '</span>' );
	}

	public function render_settings_page() {
		$this->update_lists();
		printf( '<div id="cfw-plugin-settings">' );
		printf( '<form action="options.php" method="post">' );
		settings_fields( 'creamailer-for-woocommerce' );
		do_settings_sections( 'creamailer-for-woocommerce' );
		submit_button();
		printf( '</form>' );
		printf( '<hr />' );
		printf( '<h1>' . esc_html__( 'Need help?', 'creamailer-for-woocommerce' ) . '</h1>' );
		printf( '<a href="https://tuki.creamailer.fi/hc/fi/articles/360021562680">' . esc_html__( 'See the official help article', 'creamailer-for-woocommerce' ) . '</a>' );
		printf(
			'<p><a href="%s/log/%s.log">' . esc_html__( 'View error log', 'creamailer-for-woocommerce' ) . '</a></p>',
			CREAMAILER_FOR_WOOCOMMERCE_PLUGIN_URL,
			md5( $this->settings['access_token'] )
		);
		printf( '</div>' );
	}
}
