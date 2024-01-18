<?php

namespace CodeWpAi\CodewpHelper;

class CodeWpAiAjax
{

    public function __construct()
    {
        new \CodeWpAi\CodewpHelper\Utils\CodeWpAiRegisterAjaxMethod(
            'codewpai/save-api-token',
            [$this, 'saveApiToken']
        );

        new \CodeWpAi\CodewpHelper\Utils\CodeWpAiRegisterAjaxMethod(
            'codewpai/api-auto-synchronize-save',
            [$this, 'saveAutoSynchronize']
        );

        new \CodeWpAi\CodewpHelper\Utils\CodeWpAiRegisterAjaxMethod(
            'codewpai/notice-hide',
            /**
             * Save the auto synchronize option
             */
            [$this, 'hideNotice']
        );
    }

    public function saveApiToken(): array
    {

        $token = sanitize_text_field(wp_unslash($_POST['token'] ?? ''));

        $api_key_settings = CodeWpAiSettings::getSettingsFormData(true);

        if (empty($api_key_settings['token']) && (empty($token) || 48 !== strlen($token))) {
            throw new \Exception(esc_html(__('Please enter a valid token', 'codewpai')));
        }

        $response = CodeWpAiSettings::sendDataToCodewp('POST', $token);

        CodeWpAiSettings::save(
            array(
                'token'            => $token,
                'project_id'       => $response['id'],
                'project_name'     => $response['name'],
                'auto_synchronize' => true,
                'synchronized_at'  => gmdate('Y-m-d H:i:s'),
            )
        );
        CodeWpAiCron::addCronJob();

        return CodeWpAiSettings::getSettingsFormData();
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
            $response = CodeWpAiSettings::sendDataToCodewp('PATCH');

            CodeWpAiSettings::save(
                array(
                    'project_id'       => $response['id'],
                    'project_name'     => $response['name'],
                    'auto_synchronize' => true,
                    'synchronized_at'  => gmdate('Y-m-d H:i:s'),
                )
            );

            CodeWpAiCron::addCronJob();

            return CodeWpAiSettings::getSettingsFormData(false, 'Your project will be synchronized with CodeWP');
        }
        CodeWpAiSettings::save(
            array(
                'auto_synchronize' => false,
            )
        );

        CodeWpAiCron::removeCronJob();

        return CodeWpAiSettings::getSettingsFormData(false, 'Your project will not be synchronized with CodeWP anymore');
    }


    public function hideNotice(): array
    {

        update_option('codewpai/notice_visible', 0, false);

        return [
            'notice_visible' => false,
        ];
    }
}
