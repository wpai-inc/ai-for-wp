import type {Settings} from 'types';

declare const jQuery: any;
declare const ajaxurl: string;
declare const CODEWPAI_SETTINGS: Settings;

const codewpaiRequest = async ({action, data, addNotification}) => {

    try {
        const response = await jQuery.ajax(
            {
                type: 'POST',
                url: ajaxurl,
                data: {
                    action,
                    ...data,
                    _wpnonce: CODEWPAI_SETTINGS.nonce,
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

export default codewpaiRequest;