import React, {useState} from 'react';
import {__} from '@wordpress/i18n';
import {Layout} from './components/Layout';
import {Home} from './pages/Home';
import {Settings} from './pages/Settings';
import './styles/styles.css';
import {PagePropsContext} from './hooks/usePagePropsContext';
import {Settings} from "./types";
import {NotificationsProvider} from "./hooks/useNotificationsContext";

declare const CODEWPAI_SETTINGS: Settings;

const App = () => {
    const [selectedTab, setSelectedTab] = useState('home'); // Initial tab is 'home'
    return (
        <PagePropsContext.Provider value={CODEWPAI_SETTINGS}>
            <NotificationsProvider>
                <Layout
                    title={__('CodeWP Helper', 'codewpai')}
                    tabs={[
                        {
                            name: 'home',
                            title: __('Home', 'codewpai')
                        },
                        {
                            name: 'settings',
                            title: __('Settings', 'codewpai'),
                        },
                    ]}
                    selectedTab={selectedTab}
                    setSelectedTab={setSelectedTab}
                >
                    {({selectedTab}) => (
                        <>
                            {selectedTab === 'home' && <Home handleTabChange={setSelectedTab}/>}
                            {selectedTab === 'settings' && <Settings/>}
                        </>
                    )}
                </Layout>
            </NotificationsProvider>
        </PagePropsContext.Provider>
    );
};

export default App;
