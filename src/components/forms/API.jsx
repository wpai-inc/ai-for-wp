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
	__experimentalText as Text,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { combine, createEffect, createEvent, createStore, sample } from 'effector';
import { useStore } from 'effector-react';
import { backendRequest } from '../../utils/backendRequest';
import { addNotice } from '../Notifications';

const initialFormData = CWPAI_SETTINGS['simple_form'] ?? {};

const nameChanged = createEvent();
const descriptionChanged = createEvent();
const throwErrorChanged = createEvent();
const resetForm = createEvent();
const $name = createStore(initialFormData.name ?? '');
const $description = createStore(initialFormData.description ?? '');
const $throwError = createStore(false);

$name.on(nameChanged, (_, v) => v).reset(resetForm);
$description.on(descriptionChanged, (_, v) => v).reset(resetForm);
$throwError.on(throwErrorChanged, (_, v) => v).reset(resetForm);

const saveToServer = createEffect(({ name, description, throwError }) =>
	backendRequest({
		action: 'cwpai-settings/simple_form-save',
		data: { name, description, throwError },
	})
);

const doSave = createEvent();
sample({
	clock: doSave,
	source: combine($name, $description, $throwError, (name, description, throwError) => ({
		name,
		description,
		throwError,
	})),
	target: saveToServer,
});

sample({
	clock: saveToServer.done,
	fn: () => ({ content: __('CodeWP Project API Key Saved.', 'wp-cwpai-settings-page') }),
	target: addNotice,
});

export const APIForm = () => {
	const name = useStore($name);
	const description = useStore($description);
	const throwError = useStore($throwError);
	const isSaving = useStore(saveToServer.pending);

	return (
		<Card>
			<CardHeader>
				<Heading>{__('Settings', 'wp-cwpai-settings-page')}</Heading>
			</CardHeader>
			<CardBody>
				<Disabled isDisabled={isSaving}>
					<TextControl
						help={__(
							"Get it in your project's settings page. The URL must match {{site_url}}.",
							'wp-cwpai-settings-page'
						)}
						label={__('CodeWP Project API Key', 'wp-cwpai-settings-page')}
						onChange={nameChanged}
						value={name}
						type="password"
					/>
					<CheckboxControl
						label={__('Sync to my project.', 'wp-cwpai-settings-page')}
						checked={throwError}
						onChange={throwErrorChanged}
					/>
					{isSaving && (
						<div className="spinner-wrap-overlay">
							<Spinner />
						</div>
					)}
				</Disabled>
			</CardBody>
			<CardFooter>
				<Flex>
					<Button variant="tertiary" onClick={resetForm} disabled={isSaving}>
						{__('Clear', 'wp-cwpai-settings-page')}
					</Button>
					<Button variant="primary" onClick={doSave} disabled={isSaving}>
						{__('Save', 'wp-cwpai-settings-page')}
					</Button>
				</Flex>
			</CardFooter>
		</Card>
	);
};
