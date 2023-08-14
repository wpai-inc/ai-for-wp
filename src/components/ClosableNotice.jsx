import { Notice, __experimentalSpacer as Spacer } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { createEffect, createStore } from 'effector';
import { useStore } from 'effector-react';
import { backendRequest } from '../utils/backendRequest';

const $noticeVisible = createStore(!CWPAI_SETTINGS.notice_hidden);
const doHideNotice = createEffect(() =>
	backendRequest({
		action: 'cwpai-settings/hide-notice',
	})
);
$noticeVisible.on(doHideNotice.doneData, (_, result) => !result);

export const ClosableNotice = () => {
	const noticeVisible = useStore($noticeVisible);
	return (
		<>
			{noticeVisible && (
				<>
					<Notice onRemove={doHideNotice}>
						{__("Welcome to CodeWP. We're glad you'e here.", 'wp-cwpai-settings-page')}
					</Notice>
					<Spacer marginBottom={6} />
				</>
			)}
		</>
	);
};
