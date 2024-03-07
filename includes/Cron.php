<?php

namespace WpAi\CodeWpHelper;

class Cron {

	/**
	 *  Cron constructor.
	 */
	public function __construct() {
		add_action( 'codewpai_cron_synchronizer', array( $this, 'synchronizerJob' ) );
	}

	/**
	 * Synchronize the project with CodeWP
	 *
	 * @throws \Exception If the token is not valid.
	 */
	public function synchronizerJob(): void {

		$response_body = Settings::sendDataToCodewp( 'PATCH' );

		Settings::save(
			array(
				'project_id'       => $response_body['id'],
				'project_name'     => $response_body['name'],
				'auto_synchronize' => true,
				'synchronized_at'  => gmdate( 'Y-m-d H:i:s' ),
			)
		);
	}

	/**
	 * Add the cron job.
	 *
	 * @return void
	 */
	public static function addCronJob(): void {
		if ( ! wp_next_scheduled( 'codewpai_cron_synchronizer' ) ) {
			wp_schedule_event( time(), 'daily', 'codewpai_cron_synchronizer' );
		}
	}

	/**
	 * Remove the cron job.
	 *
	 * @return void
	 */
	public static function removeCronJob(): void {
		wp_clear_scheduled_hook( 'codewpai_cron_synchronizer' );
	}
}
