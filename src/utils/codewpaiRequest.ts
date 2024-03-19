import type {SettingsType} from "../types";

declare const jQuery: any;
declare const ajaxurl: string;
declare const CODEWPAI_SETTINGS: SettingsType;

const codewpaiRequest = async ({action, method = 'POST', data, addNotification}) => {

    try {
        const response = await jQuery.ajax(
            {
                type: method,
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

        if (response.data?.message) {
            addNotification(response.data.message);
        }

        return response.data;
    } catch (err) {
        console.error(err)
        addNotification(err.message || err.statusText, 'error');
    }
};

export default codewpaiRequest;