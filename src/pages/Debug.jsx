import {
	__experimentalHeading as Heading,
	__experimentalSpacer as Spacer,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const Debug = () => {
	return (
		<>
			<Heading>{__('Debug', 'wp-modern-settings-page-boilerplate')}</Heading>
			<Spacer marginBottom={6} />
			Content that is sent goes here.
			<Spacer marginBottom={6} />
		</>
	);
};
