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
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import CwpGuide from '../components/CwpGuide';
import { useEffect, useState } from 'react';
import { LightAsync as SyntaxHighlighter } from 'react-syntax-highlighter';
import { atomOneDark } from 'react-syntax-highlighter/dist/esm/styles/hljs';

export const Snippets = () => {
	const [snippets, setSnippets] = useState([]);
	const [previewingCode, setPreviewingCode] = useState(false);
	const [previewSnippet, setPreviewSnippet] = useState({});

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
					<Heading>{__('Snippets', 'codewpai')}</Heading>
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
			<CwpGuide />
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
