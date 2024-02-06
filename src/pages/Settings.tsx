import { Notice, __experimentalSpacer as Spacer } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { TokenApiForm } from '../components/forms/ApiKey';
import { useContext } from 'react';
import { PagePropsContext } from '../hooks/usePagePropsContext';

export const Settings = () => {
	const pageProps = useContext(PagePropsContext);
	return (
		<>
			<Spacer marginBottom={6} />
			{pageProps.playground_mode ? (
				<Notice
					politeness="assertive"
					status='warning'
					spokenMessage={__('This functionality is disabled in playground mode.')}
				>
					{__('This functionality is disabled in playground mode.')}
				</Notice>
			) : (
				<TokenApiForm />
			)}

			<Spacer marginBottom={6} />
		</>
	);
};
