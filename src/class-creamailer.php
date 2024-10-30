<?php
if ( ! class_exists( 'CfwLogger' ) ) {
	require_once dirname( __FILE__ ) . '/class-cfwlogger.php';
}
class Creamailer {
	private $creamailer_api_base_url = 'https://api.cmfile.net/v1/api';
	private $creamailer_accept_header = 'application/json; version=v1';
	private $access_token;
	private $body;
	private $header;
	private $list_id;
	private $shared_secret;
	private $signature;
	private $timestamp;
	private $url;
	private $error_log;

	function __construct( $access_token, $shared_secret, $list_id = 0, $error_log = false ) {
		$this->access_token  = $access_token;
		$this->shared_secret = $shared_secret;
		$this->list_id       = $list_id;
		$this->header        = array();
		$this->timestamp     = time();
		$this->error_log     = $error_log;
	}

	private function log_error( $error ) {
		if ( ! $this->error_log ) {
			return;
		}
		$log = new CfwLogger();
		$log->log( $error );
	}

	private function gen_header() {
		$this->gen_signature();
		$this->header                        = array();
		$this->header['X-Access-Token']      = $this->access_token;
		$this->header['X-Request-Signature'] = $this->signature;
		$this->header['X-Request-Timestamp'] = $this->timestamp;
		$this->header['Accept']              = $this->creamailer_accept_header;
		$this->header['Content-Type']        = 'application/json';
	}

	/**
	 * Does HTTP POST to $this->url
	 * before calling set body $this->body
	 */
	private function post() {
		$this->gen_header();
		$res = wp_remote_post(
			$this->url,
			array(
				'headers'  => $this->header,
				'body'     => $this->body,
				'blocking' => false,
			)
		);
		if ( is_object( $res ) || 201 !== $res['response']['code'] ) {
			$this->log_error( var_export( $res, true ) );
		}
	}

	/**
	 * Does HTTP GET to $this->url
	 *
	 * @return array || null
	 */
	private function get() {
		$this->body = '';
		$this->gen_header();
		$res = wp_remote_get( $this->url, array( 'headers' => $this->header ) );
		if ( is_object( $res ) || 200 !== $res['response']['code'] ) {
			$this->log_error( var_export( $res, true ) );
			return null;
		}
		return( $res['body'] );
	}

	/**
	 * See https://tuki.creamailer.fi/hc/fi/articles/115002005031-API-kutsun-tekeminen#tunnistautuminen
	 *
	 * @return void
	 */
	private function gen_signature() {
		$this->signature = sha1(
			$this->url .
			$this->body .
			$this->timestamp .
			$this->shared_secret
		);
	}

	public function add_subscriber( $email, $name, $send_autoresponders = 0, $send_autoresponders_if_exists = 0 ) {
		/* sanity check */
		$send_autoresponders           = $send_autoresponders ? '1' : '0';
		$send_autoresponders_if_exists = $send_autoresponders_if_exists ? '1' : '0';
		if ( ! $send_autoresponders && $send_autoresponders_if_exists ) {
			$send_autoresponders_if_exists = '0';
		}

		$this->body = wp_json_encode(
			array(
				'email'                         => $email,
				'name'                          => $name,
				'send_autoresponders'           => $send_autoresponders,
				'send_autoresponders_if_exists' => $send_autoresponders_if_exists,
			)
		);

		if ( null === $this->body ) {
			$this->log_error( 'JSON decode error on add_subscriber() email=' . $email . 'name = ' . $name );
			return;
		}

		$this->url = $this->creamailer_api_base_url . '/subscribers/' . $this->list_id . '.json';
		$this->post();
	}

	/**
	 * Returns array of subscribers on $this->list
	 *
	 * @return array || null
	 * */
	public function get_subscribers() {
		$this->url = $this->creamailer_api_base_url . '/lists/subscribers/' . $this->list_id . '.json';
		$res       = $this->get();
		if ( ! $res ) {
			return( null );
		}
		$json = json_decode( $res, true );
		$ret  = array();
		foreach ( $json['Results'] as $i ) {
			array_push( $ret, $i );
		}
		return( $ret );
	}

	/**
	 * Returns available postinglists
	 *
	 * @return array || null
	 * */
	public function get_lists() {
		$this->url = $this->creamailer_api_base_url . '/lists.json';
		$res       = $this->get();
		if ( ! $res ) {
			return( null );
		}
		$json = json_decode( $res, true );
		$ret  = array();
		foreach ( $json['Results'] as $i ) {
			array_push( $ret, $i );
		}
		return( $ret );
	}
}
