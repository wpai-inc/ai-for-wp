import {
	Card,
	CardBody,
	CardHeader,
	DropdownMenu,
	Flex,
	FlexBlock,
	FlexItem,
	MenuGroup,
	MenuItem,
	TextareaControl,
	TextControl,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { close, moreVertical, reset } from '@wordpress/icons';
import { changeItem, removeItem, resetItem } from './state';

export const RepeatedItem = ({ item }) => {
	const { id, title, description, priority } = item;

	return (
		<Card size="small">
			<CardHeader isShady>
				<Flex align={'center'}>
					<FlexBlock>{title}</FlexBlock>
					<FlexItem>
						<Flex align={'center'}>
							<DropdownMenu
								icon={moreVertical}
								label={__('Actions', 'wp-cwpai-settings-page-boilerplate')}
								toggleProps={{
									isSmall: true,
									iconSize: 16,
								}}
							>
								{({ onClose }) => (
									<>
										<MenuGroup>
											{resetItem && (
												<MenuItem
													icon={reset}
													onClick={() => {
														resetItem(id);
														onClose();
													}}
												>
													{__(
														'Reset',
														'wp-cwpai-settings-page-boilerplate'
													)}
												</MenuItem>
											)}
											<MenuItem
												icon={close}
												onClick={() => {
													removeItem(id);
													onClose();
												}}
											>
												{__('Remove', 'wp-cwpai-settings-page-boilerplate')}
											</MenuItem>
										</MenuGroup>
									</>
								)}
							</DropdownMenu>
						</Flex>
					</FlexItem>
				</Flex>
			</CardHeader>
			<CardBody>
				<TextControl
					label={__('Widget Title', 'wp-cwpai-settings-page-boilerplate')}
					onChange={(v) => changeItem([id, 'title', v])}
					value={title ?? ''}
				/>
				<TextareaControl
					label={__('Widget Content', 'wp-cwpai-settings-page-boilerplate')}
					help={__(
						'You can use HTML markup with links.',
						'wp-cwpai-settings-page-boilerplate'
					)}
					value={description ?? ''}
					rows="4"
					onChange={(v) => changeItem([id, 'description', v])}
				/>
				<ToggleGroupControl
					label={__('Priority', 'wp-cwpai-settings-page-boilerplate')}
					value={priority ?? ''}
					onChange={(v) => changeItem([id, 'priority', v])}
					isBlock
				>
					<ToggleGroupControlOption
						value=""
						label={__('Top', 'wp-cwpai-settings-page-boilerplate')}
					/>
					<ToggleGroupControlOption
						value="high"
						label={__('High', 'wp-cwpai-settings-page-boilerplate')}
					/>
					<ToggleGroupControlOption
						value="default"
						label={__('Default', 'wp-cwpai-settings-page-boilerplate')}
					/>
					<ToggleGroupControlOption
						value="low"
						label={__('Low', 'wp-cwpai-settings-page-boilerplate')}
					/>
				</ToggleGroupControl>
			</CardBody>
		</Card>
	);
};
