<?php

namespace WpaiInc\CodewpHelper;

class Cron
{
    public function __construct()
    {
        add_action('cwpai_cron_synchronizer', [$this, 'synchronizerJob']);
    }

    /**
     * @throws \Exception
     */
    public function synchronizerJob()
    {

        $response_body = Settings::sendDataToCodewp('PATCH');

        Settings::save(
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
        if (! wp_next_scheduled('cwpai_cron_synchronizer')) {
            wp_schedule_event(time(), 'daily', 'cwpai_cron_synchronizer');
        }
    }

    public static function removeCronJob()
    {
        wp_clear_scheduled_hook('cwpai_cron_synchronizer');
    }
}
