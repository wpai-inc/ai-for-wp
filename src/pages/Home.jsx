import {
	Button,
	Card,
	CardBody,
	CardDivider,
	CardFooter,
	CardHeader,
	__experimentalHeading as Heading,
	__experimentalSpacer as Spacer,
	__experimentalText as Text,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import { ClosableNotice } from '../components/ClosableNotice';
import section1 from '../content/home/section1.md';
import section2 from '../content/home/section2.md';

export const Home = ({ handleTabChange }) => {
	return (
		<>
			<ClosableNotice />
			<Card>
				<CardHeader>
					<Heading>{__('Welcome to CodeWP, {{name}}', 'wp-cwpai-settings-page')}</Heading>
				</CardHeader>
				<CardBody>
					<ReactMarkdown
						className="reset-styles"
						children={section1}
						remarkPlugins={[remarkGfm]}
					/>
				</CardBody>
				<CardDivider />
				<CardBody>
					<ReactMarkdown
						className="reset-styles"
						children={section2}
						remarkPlugins={[remarkGfm]}
					/>
				</CardBody>
				<CardFooter className="components-card-footer--sticky">
					<Text>{__('Ready to get started?', 'wp-cwpai-settings-page')}</Text>
					<Button variant="secondary" onClick={() => handleTabChange('settings')}>
						{__('Set API Key', 'wp-cwpai-settings-page')}
					</Button>
				</CardFooter>
			</Card>
			<Spacer marginBottom={10} />
		</>
	);
};
