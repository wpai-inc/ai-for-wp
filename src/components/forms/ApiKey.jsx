import {
	Button,
	Card,
	CardBody,
	CardFooter,
	CardHeader,
	CheckboxControl,
	Disabled,
	Flex,
	FlexBlock,
	Spinner,
	TextControl,
	__experimentalHeading as Heading,
	__experimentalText as Text, ToggleControl,
} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {combine, createEffect, createEvent, createStore, sample} from 'effector';
import {useStore} from 'effector-react';
import {backendRequest} from '../../utils/backendRequest';
import {addNotice} from '../Notifications';

const initialFormData = CWPAI_SETTINGS['api_key_form'] ?? {
	token: '',
	project_id: '',
	project_name: '',
	synchronized_at: '',
	auto_synchronize: true,
};

const codewp_server = CWPAI_SETTINGS['codewp_server'];

const saveToServer = createEffect(({token, autoSynchronize, throwError}) =>
	backendRequest({
		action: 'cwpai-settings/api-token-save',
		data: {token, autoSynchronize, throwError},
	})
);
const toggleAutoSynchronize = createEffect(({autoSynchronize}) =>
	backendRequest({
		action: 'cwpai-settings/api-auto-synchronize-save',
		data: {autoSynchronize},
	})
);
const $token = createStore('');
const $tokenPlaceholder = createStore(initialFormData.token ?? '');
const $projectId = createStore(initialFormData.project_id ?? '');
const $projectName = createStore(initialFormData.project_name ?? '');
const $synchronizedAt = createStore(initialFormData.synchronized_at ?? '');
const $autoSynchronize = createStore(initialFormData.auto_synchronize ?? true);
const $throwError = createStore(false);

const tokenChanged = createEvent();
const projectNameChanged = createEvent();
const synchronizedAtChanged = createEvent();
const autoSynchronizeChange = createEvent();
const throwErrorChanged = createEvent();
const doSave = createEvent();

$token
	.on(tokenChanged, (_, token) => token)
	.on(saveToServer.doneData, (_, {result}) => '');

$tokenPlaceholder
	.on(saveToServer.doneData, (_, {token}) => token);

$projectId
	.on(saveToServer.doneData, (_, {project_id}) => project_id);

$projectName
	.on(saveToServer.doneData, (_, {project_name}) => project_name)
	.on(projectNameChanged, (_, projectName) => projectName)
	.on(toggleAutoSynchronize.doneData, (_, {projectName}) => projectName);



$synchronizedAt
	.on(saveToServer.doneData, (_, {synchronized_at}) => synchronized_at)
	.on(synchronizedAtChanged, (_, synchronizedAt) => synchronizedAt)
	.on(toggleAutoSynchronize.doneData, (_, {synchronized_at}) => synchronized_at);

$autoSynchronize
	.on(autoSynchronizeChange, (_, autoSynchronize) => autoSynchronize)
	.on(saveToServer.doneData, (_, {auto_synchronize}) => auto_synchronize)
	.on(toggleAutoSynchronize.doneData, (_, {auto_synchronize}) => auto_synchronize);
$throwError.on(throwErrorChanged, (_, throwError) => throwError);


sample({
	clock: doSave,
	source: combine($token, (token) => ({
		token,
	})),
	target: saveToServer,
});

sample({
	clock: autoSynchronizeChange,
	source: combine($autoSynchronize, (autoSynchronize) => ({
		autoSynchronize,
	})),
	target: toggleAutoSynchronize,
});

sample({
	clock: toggleAutoSynchronize.done,
	fn: (data) => {
		console.log('toggleAutoSynchronize.done', data)
		const message = data.result.auto_synchronize
			? __('CodeWP Project Auto Synchronize Enabled.', 'wp-cwpai-settings-page')
			: __('CodeWP Project Auto Synchronize Disabled.', 'wp-cwpai-settings-page');

		return ({content: message})
	},
	target: addNotice,
});

sample({
	clock: saveToServer.done,
	fn: () => ({content: __('CodeWP Project API Key Saved.', 'wp-cwpai-settings-page')}),
	target: addNotice,
});

export const TokenApiForm = () => {

	const token = useStore($token);
	const tokenPlaceholder = useStore($tokenPlaceholder);
	const projectId = useStore($projectId);
	const projectName = useStore($projectName);
	const synchronizedAt = useStore($synchronizedAt);
	const autoSynchronize = useStore($autoSynchronize);
	const throwError = useStore($throwError);
	const isSaving = useStore(saveToServer.pending);

	return (
		<Card>
			<CardHeader>
				<Heading>{__('API Key', 'wp-cwpai-settings-page')}</Heading>
				<Button variant="primary" target='_blank' href={codewp_server + '/user/api-tokens'}>
					{__('Get your api key', 'wp-cwpai-settings-page')}
				</Button>
			</CardHeader>
			<CardBody>
				<p>{__('Click the button above to get you API key. You can use this API key to sync your project with CodeWP.', 'wp-cwpai-settings-page')}</p>
				<p>{__('After you get your API key, add it on the field below and click save', 'wp-cwpai-settings-page')}</p>
			</CardBody>
			<CardBody>
				<Disabled isDisabled={isSaving}>
					<Flex align='end' className="mb-1.5">
						<FlexBlock>
							<TextControl
								label={__('CodeWP Project API Key', 'wp-cwpai-settings-page')}
								onChange={tokenChanged}
								value={token}
								placeholder={tokenPlaceholder}
								type="text"
							/>
						</FlexBlock>
						<Button variant="primary" className="mb-1.5" onClick={doSave} disabled={isSaving}>
							{__('Save', 'wp-cwpai-settings-page')}
						</Button>
					</Flex>

					{tokenPlaceholder && (
						<ToggleControl
							label="Auto syncronize"
							help={ __('If enabled, the plugin will automatically synchronize your project with CodeWP.', 'wp-cwpai-settings-page') }
							checked={ autoSynchronize }
							onChange={ autoSynchronizeChange }
						/>
					)}

					{isSaving && (
						<div className="spinner-wrap-overlay">
							<Spinner/>
						</div>
					)}
				</Disabled>

				<hr/>

				<Flex direction='column' gap="2">
				{projectId && projectName && (
					<div>{__('Project', 'wp-cwpai-settings-page')}: <a
						href={codewp_server + '/projects/' + projectId} target="_blank"><strong>{projectName}</strong> <span className="text-gray text-sm">({projectId})</span></a>
					</div>
				)}

				{synchronizedAt && (
					<div>{__('Last synchronized', 'wp-cwpai-settings-page')}: {synchronizedAt}</div>
				)}
				</Flex>
			</CardBody>
		</Card>
	);
};
