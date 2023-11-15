import $ from 'jquery';
import type {CwpaiSettings} from 'types';

declare const ajaxurl: string;
declare const CWPAI_SETTINGS: CwpaiSettings;

const cwpaiRequest = async ({action, data, addNotification}) => {

    try {
        const response = await $.ajax(
            {
                type: 'POST',
                url: ajaxurl,
                data: {
                    action,
                    ...data,
                    _wpnonce: CWPAI_SETTINGS.nonce,
                },
            }
        );

        if (!response.success) {
            addNotification(response.data?.error ?? response.data ?? 'Error', 'error');
        }

        if (response.data.message) {
            addNotification(response.data.message);
        }

        return response.data;
    } catch (err) {
        console.log(err)
        addNotification(err.message || err.statusText, 'error');
    }
};

export default cwpaiRequest;