import {
    __experimentalHeading as Heading,
    __experimentalSpacer as Spacer,
    __experimentalText as Text,
    Button,
    Card,
    CardBody,
    CardDivider,
    CardFooter,
    CardHeader,
} from '@wordpress/components';
import {__} from '@wordpress/i18n';

import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import section1 from '../content/home/section1.md';
import section2 from '../content/home/section2.md';
import {useContext} from "react";
import {PagePropsContext} from '../hooks/usePagePropsContext';
import {NotificationsContext} from "../hooks/useNotificationsContext";
import {ClosableNotice} from "../components/ClosableNotice";

export const Home = ({handleTabChange}) => {

    const {user} = useContext(PagePropsContext);

    const {notifications, addNotification} = useContext(NotificationsContext);

    return (
        <>
            <ClosableNotice/>
            <Card>
                <CardHeader>
                    <Heading>{__('Welcome to CodeWP, ' + user.name, 'wp-cwpai-settings-page')}</Heading>
                </CardHeader>

                <CardBody>
                    <ReactMarkdown
                        className="cwpai-reset-styles"
                        children={section1}
                        remarkPlugins={[remarkGfm]}
                    />
                </CardBody>
                <CardDivider/>
                <CardBody>
                    <ReactMarkdown
                        className="cwpai-reset-styles"
                        children={section2}
                        remarkPlugins={[remarkGfm]}
                    />
                </CardBody>
                <CardFooter className="cwpai-components-card-footer--sticky">
                    <Text>{__('Ready to get started?', 'wp-cwpai-settings-page')}</Text>
                    <Button className="is-secondary" variant="secondary" onClick={() => handleTabChange('settings')}>
                        {__('Set API Key', 'wp-cwpai-settings-page')}
                    </Button>
                </CardFooter>
            </Card>
            <Spacer marginBottom={10}/>
        </>
    );
};
