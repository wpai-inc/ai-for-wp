import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Layout } from './components/Layout';
import { Home } from './pages/Home';
import { Settings } from './pages/Settings';
import { Debug } from './pages/Debug';
import './styles/styles.css';

export const App = () => {
	const [selectedTab, setSelectedTab] = useState('home'); // Initial tab is 'home'

	return (
		<Layout
			title={__('CodeWP Helper', 'wp-cwpai-settings-page')}
			tabs={[
				{ name: 'home', title: __('Home', 'wp-cwpai-settings-page') },
				{
					name: 'settings',
					title: __('Settings', 'wp-cwpai-settings-page'),
				},
				/*{
					name: 'help',
					title: __('Help', 'wp-cwpai-settings-page'),
				},*/
			]}
		>
			{({ selectedTab }) => (
				<>
					{selectedTab === 'home' && <Home />}
					{selectedTab === 'settings' && <Settings />}
					{selectedTab === 'help' && <Debug />}
				</>
			)}
		</Layout>
	);
};
