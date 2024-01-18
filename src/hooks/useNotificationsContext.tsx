import {createContext, useContext, useState} from "react";
import {nextId} from "../utils/nextId";
import type {NotificationContext, Notifications} from "../types";

export const NotificationsContext = createContext<NotificationContext | undefined>(undefined);

export const useNotificationsContext = () => {
    const notifications = useContext(NotificationsContext);
    if (notifications === undefined) {
        throw new Error('useNotifications must be used within a NotificationsContext');
    }
    return notifications;
}

export const NotificationsProvider = ({children}) => {

    const [notifications, setNotifications] = useState<Notifications>([]);

    const addNotification = (notification: string, type: string = 'default') => {
        let className = 'codewpai-default-notification';
        if (type === 'error') {
            className = 'codewpai-error-notification';
        }

        setNotifications([
            ...notifications,
            {
                id: nextId(),
                content: notification,
                className: className,
            }
        ]);

    }

    const removeNotification = (id: string) => setNotifications(notifications.filter((notification) => notification.id !== id))

    return (
        <NotificationsContext.Provider value={{
            notifications,
            addNotification,
            removeNotification
        }}>
            {children}
        </NotificationsContext.Provider>
    );
}
