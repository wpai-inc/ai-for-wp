import { Guide } from '@wordpress/components';
import { forwardRef, useContext, useImperativeHandle, useState } from 'react';
import { PagePropsContext } from '../hooks/usePagePropsContext';
import Cookies from 'js-cookie';

const CwpHelper = forwardRef((props, ref) => {
	const pageProps = useContext(PagePropsContext);
	const cwp_playground_guide_cookie = Cookies.get('cwp_playground_guide');

	const [playgroundGuideOpen, setPlaygroundGuideOpen] = useState(
		pageProps.playground_mode && cwp_playground_guide_cookie !== 'closed'
	);

	function onClosePlaygroundGuide() {
		setPlaygroundGuideOpen(false);
		Cookies.set('cwp_playground_guide', 'closed', { expires: 365 });
	}

	useImperativeHandle(ref, () => ({
		openGuide() {
			setPlaygroundGuideOpen(true);
		},
	}));

	if (!playgroundGuideOpen) {
		return null;
	}

	return (
		<Guide
			onFinish={() => onClosePlaygroundGuide()}
			className="codewpai-preview-guide"
			pages={[
				{
					image: <img src={pageProps.plugin_url + '/assets/cwp-helper-slide-1.jpg'} />,
					content: (
						<div>
							<h3>Welcome to CodeWP's Preview Feature</h3>
							<p>
								We have spun up a live WordPress instance and added an index.php
								file to the website. Now, you can verify that your code works as
								expected before installing it on a staging or production website.
							</p>
						</div>
					),
				},
				{
					image: <img src={pageProps.plugin_url + '/assets/cwp-helper-slide-2.jpg'} />,
					content: (
						<div>
							<h3>Manually Test the Features Your Code Adds</h3>
							<p>
								Manually test the features that your code adds to WordPress here,
								and see if any errors occur.
							</p>
						</div>
					),
				},
				{
					image: <img src={pageProps.plugin_url + '/assets/cwp-helper-slide-3.jpg'} />,
					content: (
						<div>
							<h3>Use CodeWP's AI Testing Feature</h3>
							<p>
								In the right panel, go to the Testing tab. This uses the WordPress
								instance, runs your code, and checks for any fatal errors. If any
								exist, CodeWP's AI can automatically fix them for you.
							</p>
						</div>
					),
				},
				{
					image: <img src={pageProps.plugin_url + '/assets/cwp-helper-slide-4.jpg'} />,
					content: (
						<div>
							<h3>Future Additions to the Preview</h3>
							<p>
								We'll be adding more features to the Preview, like dependent plugins
								based on mode, automatic navigation for AI testing, and more. If you
								have any suggestions, please make a feature{' '}
								<a href="https://codewp.canny.io/feature-requests">request here.</a>
							</p>
						</div>
					),
				},
			]}
			contentLabel={''}
		/>
	);
});

export default CwpHelper;
