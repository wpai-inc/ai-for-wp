import React, {useState} from 'react';
import {__} from '@wordpress/i18n';
import {Layout} from './components/Layout';
import {Home} from './pages/Home';
import {Settings} from './pages/Settings';
import './styles/styles.css';
import {PagePropsContext} from './hooks/usePagePropsContext';
import {SettingsType} from "./types";
import {NotificationsProvider} from "./hooks/useNotificationsContext";
import { Snippets } from './pages/Snippets';
import { Logs } from './pages/Logs';

declare const CODEWPAI_SETTINGS: SettingsType;

const App = () => {
    const url = new URL(window.location.href);
    const initialTab = url.searchParams.get('tab'); 
    const [selectedTab, setSelectedTab] = useState(initialTab || 'home');

    function handleTabChange(tab) {
        setSelectedTab(tab);
        url.searchParams.set('tab', tab);
        window.history.pushState({}, '', url);
    }

    return (
		<PagePropsContext.Provider value={CODEWPAI_SETTINGS}>
			<NotificationsProvider>
				<Layout
					title={__('CodeWP Helper', 'codewpai')}
					tabs={[
						{
							name: 'home',
							title: __('Home', 'codewpai'),
						},
						{
							name: 'settings',
							title: __('Settings', 'codewpai'),
						},
						{
							name: 'snippets',
							title: __('Snippets', 'codewpai'),
						},
						{
							name: 'logs',
							title: __('Logs', 'codewpai'),
						},
					]}
					selectedTab={selectedTab}
					setSelectedTab={handleTabChange}
				>
					{({ selectedTab }) => (
						<>
							{selectedTab === 'home' && <Home handleTabChange={handleTabChange} />}
							{selectedTab === 'settings' && <Settings />}
							{selectedTab === 'snippets' && <Snippets />}
							{selectedTab === 'logs' && <Logs />}
						</>
					)}
				</Layout>
			</NotificationsProvider>
		</PagePropsContext.Provider>
	);
};

export default App;
