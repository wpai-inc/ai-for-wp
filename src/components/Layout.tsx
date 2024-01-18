import {__experimentalHeading as Heading} from '@wordpress/components';
import {useMemo} from 'react';
import {Notifications} from './Notifications';

import {Tab} from './Tab';
import {Tabs} from './Tabs';

import {Icon} from './Icon';

export const Layout = ({title, children, tabs = [], selectedTab, setSelectedTab}) => {
    return (
        <div className="codewpai-layout">
            <div className="codewpai-header">
                <div className="codewpai-title-section">
                    <Icon/>
                    <Heading as="h1">{title}</Heading>
                </div>
                <Tabs value={selectedTab} onChange={(v) => setSelectedTab(v)}>
                    {tabs.map(({name, title}) => (
                        <Tab key={name} name={name}>
                            {title}
                        </Tab>
                    ))}
                </Tabs>
            </div>
            <div className="codewpai-layout-body hide-if-no-js">
                {useMemo(() => children({selectedTab}), [selectedTab])}
            </div>
            <Notifications/>
        </div>
    );
};
