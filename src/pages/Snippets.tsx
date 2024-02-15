import {
	__experimentalHeading as Heading,
	__experimentalSpacer as Spacer,
	__experimentalText as Text,
	Button,
	Card,
	CardBody,
	CardHeader,
	ToggleControl,
	Modal,
	Flex,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import CwpGuide from '../components/CwpGuide';
import { useContext, useEffect, useRef, useState } from 'react';
import { LightAsync as SyntaxHighlighter } from 'react-syntax-highlighter';
import { atomOneDark } from 'react-syntax-highlighter/dist/esm/styles/hljs';
import { PagePropsContext } from '../hooks/usePagePropsContext';

export const Snippets = () => {
	const [snippets, setSnippets] = useState([]);
	const [previewingCode, setPreviewingCode] = useState(false);
	const [previewSnippet, setPreviewSnippet] = useState({});
	const pageProps = useContext(PagePropsContext);
	const guideRef = useRef(null);

	useEffect(() => {
		fetch(ajaxurl + '?action=codewpai_get_snippets')
			.then((response) => response.json())
			.then((data) => {
				setSnippets(data);
			});
	}, []);

	function toggleSnippet(snippet_name, enabled) {
		let toggle_url = ajaxurl + '?action=codewpai_disable_snippet&snippet_name=' + snippet_name;
		if (!enabled) {
			toggle_url = ajaxurl + '?action=codewpai_enable_snippet&snippet_name=' + snippet_name;
		}
		fetch(toggle_url)
			.then((response) => response.json())
			.then((data) => {
				setSnippets(data);
			});
	}

	function handlePreview(snippet) {
		setPreviewingCode(true);
		setPreviewSnippet(snippet);
	}

	function closePreview() {
		setPreviewingCode(false);
		setPreviewSnippet({});
	}

	return (
		<>
			<Spacer marginBottom={6} />
			<Card>
				<CardHeader>
					<Heading>
						<Flex align="center" gap={2}>
							{__('Snippets', 'codewpai')}
							{pageProps.playground_mode && (
								<svg
									xmlns="http://www.w3.org/2000/svg"
									fill="none"
									viewBox="0 0 24 24"
									width={24}
									height={24}
									strokeWidth={1.5}
									stroke="#000"
									className="w-6 h-6"
									style={{ cursor: 'pointer' }}
									onClick={() => {
										guideRef.current.openGuide();
									}}
								>
									<path
										strokeLinecap="round"
										strokeLinejoin="round"
										d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"
									/>
								</svg>
							)}
						</Flex>
					</Heading>
				</CardHeader>
				<CardBody>
					{snippets.length === 0 && <Text>{__('No snippets found', 'codewpai')}</Text>}

					{snippets.length > 0 && (
						<table className="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th>Snippet name</th>
									<th style={{ width: '130px' }}>Status</th>
									<th style={{ width: '100px' }}>View</th>
								</tr>
							</thead>
							<tbody>
								{snippets.map((snippet) => (
									<tr key={snippet.id}>
										<td>{snippet.name}</td>
										<td>
											<ToggleControl
												label={snippet.enabled ? 'Enabled' : 'Disabled'}
												checked={snippet.enabled}
												onChange={() =>
													toggleSnippet(snippet.name, snippet.enabled)
												}
											/>
										</td>
										<td>
											<Button onClick={() => handlePreview(snippet)}>
												View
											</Button>
										</td>
									</tr>
								))}
							</tbody>
						</table>
					)}
				</CardBody>
			</Card>
			<CwpGuide ref={guideRef} />
			<Spacer marginBottom={6} />

			{previewingCode && previewSnippet && (
				<Modal
					title={previewSnippet.name}
					className="codewpai-snippet-preview"
					onRequestClose={closePreview}
				>
					<SyntaxHighlighter language="php" style={atomOneDark}>
						{previewSnippet.code}
					</SyntaxHighlighter>
				</Modal>
			)}
		</>
	);
};
