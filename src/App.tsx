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
import {Packages} from "./pages/Packages";

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
					title={__('CodeWP Helper', 'ai-for-wp')}
					tabs={[
						{
							name: 'home',
							title: __('Home', 'ai-for-wp'),
						},
						{
							name: 'settings',
							title: __('Settings', 'ai-for-wp'),
						},
						{
							name: 'packages',
							title: __('Packages', 'ai-for-wp'),
						},
						{
							name: 'logs',
							title: __('Logs', 'ai-for-wp'),
						},
					]}
					selectedTab={selectedTab}
					setSelectedTab={handleTabChange}
				>
					{({ selectedTab }) => (
						<>
							{selectedTab === 'home' && <Home handleTabChange={handleTabChange} />}
							{selectedTab === 'settings' && <Settings />}
							{selectedTab === 'packages' && <Packages />}
							{selectedTab === 'logs' && <Logs />}
						</>
					)}
				</Layout>
			</NotificationsProvider>
		</PagePropsContext.Provider>
	);
};

export default App;
