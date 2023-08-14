import { __experimentalHeading as Heading } from '@wordpress/components';
import { useMemo, useState } from 'react';
import { Notifications } from './Notifications';

import { Tab } from './Tab';
import { Tabs } from './Tabs';

import { Icon } from './Icon';

export const Layout = ({ title, children, tabs = [] }) => {
	const [selectedTab, setSelectedTab] = useState(tabs?.[0]?.name || '');

	return (
		<div className="cwpai-settings-layout">
			<div className="cwpai-settings-header">
				<div className="cwpai-settings-title-section">
					<Icon name="CodeWP Helper Plugin" />
					<Heading as="h1">{title}</Heading>
				</div>
				<Tabs value={selectedTab} onChange={(v) => setSelectedTab(v)}>
					{tabs.map(({ name, title }) => (
						<Tab key={name} name={name}>
							{title}
						</Tab>
					))}
				</Tabs>
			</div>
			<div className="cwpai-settings-layout-body hide-if-no-js">
				{useMemo(() => children({ selectedTab }), [selectedTab])}
			</div>
			<Notifications />
		</div>
	);
};
