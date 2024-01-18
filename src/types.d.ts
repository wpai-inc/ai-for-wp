export type Project = {
    project_id: string | null;
    project_name: string | null;
    token: string | null;
    token_placeholder: string | null;
    auto_synchronize: boolean;
    synchronized_at: string | null;
}
export type SettingsType = {
    nonce: string;
    codewp_server: string;
    notice_visible: string;
    user: {
        name: string;
    }
    project: Project
}


export type Notification = {
    id: string;
    content: string;
    className: string;
};

export type Notifications = Notification[] | null;

export type NotificationContext = {
    notifications: Notifications;
    addNotification: (notification: string, type?: string) => void;
    removeNotification: (id: string) => void;
}