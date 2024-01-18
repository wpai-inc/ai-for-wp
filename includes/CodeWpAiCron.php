<?php

namespace CodeWpAi\CodewpHelper;

class CodeWpAiCron
{
    public function __construct()
    {
        add_action('codewpai_cron_synchronizer', [$this, 'synchronizerJob']);
    }

    /**
     * @throws \Exception
     */
    public function synchronizerJob()
    {

        $response_body = CodeWpAiSettings::sendDataToCodewp('PATCH');

        CodeWpAiSettings::save(
            array(
                'project_id'       => $response_body['id'],
                'project_name'     => $response_body['name'],
                'auto_synchronize' => true,
                'synchronized_at'  => gmdate('Y-m-d H:i:s'),
            )
        );
    }

    public static function addCronJob()
    {
        if (! wp_next_scheduled('codewpai_cron_synchronizer')) {
            wp_schedule_event(time(), 'daily', 'codewpai_cron_synchronizer');
        }
    }

    public static function removeCronJob()
    {
        wp_clear_scheduled_hook('codewpai_cron_synchronizer');
    }
}
