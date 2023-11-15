<?php
/**
 * Add cron job
 *
 * @return void
 */
function cwpai_add_cron_job() {
	if ( ! wp_next_scheduled( 'cwpai_cron_synchronizer' ) ) {
		wp_schedule_event( time(), 'daily', 'cwpai_cron_synchronizer' );
	}
}

/**
 * Cron job to synchronize the project with CodeWP
 *
 * @return void
 * @throws /ImagickException If the Imagick extension is not loaded.
 */
function cwpai_helper_cron_synchronizer_job() {
	$response = cwpai_send_data_to_codewp( 'PATCH' );
	$body     = json_decode( $response['body'], true );
	if ( 200 === $response['response']['code'] || 201 === $response['response']['code'] ) {
		cwpai_save_settings(
			array(
				'project_id'      => $body['id'],
				'project_name'    => $body['name'],
				'auto_syncronize' => true,
				'synchronized_at' => date( 'Y-m-d H:i:s' ),
			)
		);
	}
}

add_action( 'cwpai_cron_synchronizer', 'cwpai_helper_cron_synchronizer_job' );
