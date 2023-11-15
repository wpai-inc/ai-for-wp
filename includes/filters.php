<?php

/**
 * Add variable to frontend.
 *
 * @param array $variables The variables.
 *
 * @return array
 */
function cwpai_add_variable_to_frontend( array $variables ): array {
	$variables['nonce'] = wp_create_nonce( NONCE_ACTION );

	return $variables;
}
add_filter( 'cwpai_settings_variables', 'cwpai_add_variable_to_frontend' );
