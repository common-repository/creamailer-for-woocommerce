<?php
class CfwLogger {
	private $logfile;

	function __construct() {
		$settings = get_option( 'creamailer-for-woocommerce' );
		$logdir   = CREAMAILER_FOR_WOOCOMMERCE_LOG_DIR;

		if ( ! is_dir( $logdir ) && ! mkdir( $logdir ) ) {
			return;
		}

		$this->logfile = $logdir . '/' . md5( $settings['access_token'] ) . '.log';
	}

	public function log( $msg ) {
		file_put_contents( $this->logfile, $msg, FILE_APPEND );
	}
}
