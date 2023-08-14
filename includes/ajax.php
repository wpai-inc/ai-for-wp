<?php

namespace CodeWPHelper;

register_ajax_method(
	'cwpai-settings/hide-notice',
	function () {
		update_option('cwpai-settings/notice_hidden', true);

		return true;
	}
);

register_ajax_method(
	'cwpai-settings/simple_form-save',
	function () {
		$name = sanitize_text_field($_POST['name']);
		$description = sanitize_text_field($_POST['description']);
		$throwError = $_POST['throwError'] === 'true';

		if (true === $throwError) {
			throw new \Exception(__('You checked the checkbox. Server error', 'wp-cwpai-settings-page'));
		}

		sleep(1);

		$data = [
			'name' => $name,
			'description' => $description,
		];

		update_option('cwpai-settings/simple_form', $data);

		return $data;
	}
);

register_ajax_method(
	'cwpai-settings/repeated_form-save',
	function () {
		$name = sanitize_text_field($_POST['name']);
		$items = $_POST['items'];

		if (!is_array($items)) {
			$items = [];
		}

		foreach ($items as &$item) {
			$item['title'] = sanitize_text_field($item['title']);
			$item['description'] = sanitize_text_field($item['description']);
		}

		$data = [
			'name' => $name,
			'items' => $items,
		];

		update_option('cwpai-settings/repeated_form', $data);

		return $data;
	}
);
