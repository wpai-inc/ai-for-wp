import {__experimentalSpacer as Spacer, Notice} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {useState} from 'react';
import cwpaiRequest from '../utils/cwpaiRequest';
import {usePagePropsContext} from "../hooks/usePagePropsContext";


export const ClosableNotice = () => {

    const {notice_visible} = usePagePropsContext();
    const [noticeVisible, setNoticeVisible] = useState(notice_visible);
    const doHideNotice = () => {
        cwpaiRequest({
            action: 'cwpai-settings/notice-hide',
            data: {},
            addNotification: () => {
            }
        });
        setNoticeVisible("0");
    }

    return (
        <>
            {noticeVisible === "1" && (
                <>
                    <Notice onRemove={doHideNotice}>
                        {__("Welcome to CodeWP. We're glad you'e here.", 'wp-cwpai-settings-page')}
                    </Notice>
                    <Spacer marginBottom={6}/>
                </>
            )}
        </>
    );
};
