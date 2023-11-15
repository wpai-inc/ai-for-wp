import { __experimentalSpacer as Spacer } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { TokenApiForm } from '../components/forms/ApiKey';

export const Settings = () => {
	return (
		<>
			<Spacer marginBottom={6} />
			<TokenApiForm />
			<Spacer marginBottom={6} />
		</>
	);
};
