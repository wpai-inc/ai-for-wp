import { __experimentalSpacer as Spacer } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { APIForm } from '../components/forms/API';

export const Settings = () => {
	return (
		<>
			<Spacer marginBottom={6} />
			<APIForm />
			<Spacer marginBottom={6} />
		</>
	);
};
