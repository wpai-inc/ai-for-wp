import {SnackbarList} from '@wordpress/components';
import {useNotificationsContext} from '../hooks/useNotificationsContext';
import {createPortal} from 'react-dom';
import {useEffect} from "react";


export const Notifications = () => {
    const notificationsContext = useNotificationsContext();

    return createPortal(<NotificationsBody/>, document.body);
};
export const NotificationsBody = () => {
    const notificationsContext = useNotificationsContext();
    useEffect(() => {
        console.log('theNotifications', notificationsContext.notifications);
    }, [notificationsContext.notifications]);

    return <SnackbarList notices={notificationsContext.notifications}
                         className="cwpai-components-snackbar-list"
                         onRemove={notificationsContext.removeNotification}/>;
};
