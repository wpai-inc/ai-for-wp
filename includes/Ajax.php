<?php

namespace WpaiInc\CodewpHelper;

class Ajax
{

    public function __construct()
    {
        new \WpaiInc\CodewpHelper\Utils\RegisterAjaxMethod(
            'cwpai-helper/save-api-token',
            [$this, 'saveApiToken']
        );

        new \WpaiInc\CodewpHelper\Utils\RegisterAjaxMethod(
            'cwpai-helper/api-auto-synchronize-save',
            [$this, 'saveAutoSynchronize']
        );

        new \WpaiInc\CodewpHelper\Utils\RegisterAjaxMethod(
            'cwpai-helper/notice-hide',
            /**
             * Save the auto synchronize option
             */
            [$this, 'hideNotice']
        );
    }

    public function saveApiToken(): array
    {

        $token = sanitize_text_field(wp_unslash($_POST['token'] ?? ''));

        $api_key_settings = Settings::getSettingsFormData(true);

        if (empty($api_key_settings['token']) && (empty($token) || 48 !== strlen($token))) {
            throw new \Exception(esc_html(__('Please enter a valid token', 'cwpai-helper')));
        }

        $response = Settings::sendDataToCodewp('POST', $token);

        Settings::save(
            array(
                'token'            => $token,
                'project_id'       => $response['id'],
                'project_name'     => $response['name'],
                'auto_synchronize' => true,
                'synchronized_at'  => gmdate('Y-m-d H:i:s'),
            )
        );
        Cron::addCronJob();

        return Settings::getSettingsFormData();
    }

    /**
     * Save the auto synchronize option
     *
     * @throws \Exception
     */
    public function saveAutoSynchronize(): array
    {

        $auto_synchronize = 'true' === (sanitize_text_field(wp_unslash($_POST['autoSynchronize'] ?? '')));

        if (true === $auto_synchronize) {
            $response = Settings::sendDataToCodewp('PATCH');

            Settings::save(
                array(
                    'project_id'       => $response['id'],
                    'project_name'     => $response['name'],
                    'auto_synchronize' => true,
                    'synchronized_at'  => gmdate('Y-m-d H:i:s'),
                )
            );

            Cron::addCronJob();

            return Settings::getSettingsFormData(false, 'Your project will be synchronized with CodeWP');
        }
        Settings::save(
            array(
                'auto_synchronize' => false,
            )
        );

        Cron::removeCronJob();

        return Settings::getSettingsFormData(false, 'Your project will not be synchronized with CodeWP anymore');
    }


    public function hideNotice(): array
    {

        update_option('cwpai-helper/notice_visible', 0, false);

        return [
            'notice_visible' => false,
        ];
    }
}
