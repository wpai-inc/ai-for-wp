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

export type SnippetErrorType = {
    file: string;
    type: number;
    line: number;
    message: string;
}

export type SnippetType = {
    name: string;
    description: string;
    code: string;
    enabled: boolean;
    error?: SnippetErrorType | null;
}

export type PackageFileType = {
    id: string;
    name: string;
    content: string;
    description: string;
    path: string;
    extension: string;
    updated_at: string;
    enabled: boolean;
}

export type PackageType = {
    name: string;
    description: string;
    id: string;
    project_id: string;
    type: number;
    updated_at: string;
    files: PackageFileType[];
    installed: boolean;
    has_enabled_snippets: boolean;
    update_available: boolean;
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