import {
	__experimentalHeading as Heading,
	__experimentalSpacer as Spacer,
	__experimentalText as Text,
	Button,
	Card,
	CardBody,
	CardHeader,
	ToggleControl,
	Guide,
	Modal,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { PagePropsContext } from '../hooks/usePagePropsContext';
import { useContext, useEffect, useState } from 'react';
import { LightAsync as SyntaxHighlighter } from 'react-syntax-highlighter';
import { atomOneDark } from 'react-syntax-highlighter/dist/esm/styles/hljs';

export const Snippets = () => {
	const pageProps = useContext(PagePropsContext);
	const [playgroundGuideOpen, setPlaygroundGuideOpen] = useState(pageProps.playground_mode);
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
			{playgroundGuideOpen && (
				<Guide
					onFinish={() => setPlaygroundGuideOpen(false)}
					className="codewpai-preview-guide"
					pages={[
						{
							content: (
								<div>
									<h3>Welcome to CodeWP's Preview Feature</h3>
									<p>
										We have spun up a live WordPress instance and added an
										index.php file to the website. Now, you can verify that your
										code works as expected before installing it on a staging or
										production website.
									</p>
								</div>
							),
						},
						{
							content: (
								<div>
									<h3>Manually Test the Features Your Code Adds</h3>
									<p>
										Manually test the features that your code adds to WordPress
										here, and see if any errors occur.
									</p>
								</div>
							),
						},
						{
							content: (
								<div>
									<h3>Use CodeWP's AI Testing Feature</h3>
									<p>
										In the right panel, go to the Testing tab. This uses the
										WordPress instance, runs your code, and checks for any fatal
										errors. If any exist, CodeWP's AI can automatically fix them
										for you.
									</p>
								</div>
							),
						},
						{
							content: (
								<div>
									<h3>Future Additions to the Preview</h3>
									<p>
										We'll be adding more features to the Preview, like dependent
										plugins based on mode, automatic navigation for AI testing,
										and more. If you have any suggestions, please make a feature{' '}
										<a href="https://codewp.canny.io/feature-requests">
											request here.
										</a>
									</p>
								</div>
							),
						},
					]}
					contentLabel={''}
				/>
			)}
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
