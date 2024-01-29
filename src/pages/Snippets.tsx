import { __experimentalSpacer as Spacer } from '@wordpress/components';
import { useEffect, useState } from 'react';

export const Snippets = () => {

    const [snippets, setSnippets] = useState([]);

    useEffect(() => {
        fetch(ajaxurl + '?action=codewpai_get_snippets')
            .then(response => response.json())
            .then(data => {
                setSnippets(data);
            });
    }, []);

    function enableSnippet(snippet_name) {
		fech(ajaxurl + '?action=codewpai_enable_snippet&snippet_name=' + snippet_name)
            .then(response => response.json())
            .then(data => {
                setSnippets(data);
            });
    }

    function disableSnippet(snippet_name) {
        fetch(ajaxurl + '?action=codewpai_disable_snippet&snippet_name=' + snippet_name)
            .then(response => response.json())
            .then(data => {
                setSnippets(data);
            });
    }

	return (
		<>
			<Spacer marginBottom={6} />
			Snippets:
			<ul>
				{snippets.map((snippet) => (
					<li key={snippet.id}>
						<strong>{snippet.name}</strong>
						&nbsp; ({snippet.enabled ? 'enabled' : 'disabled'}) &nbsp;
						{snippet.enabled ? <button>Disable</button> : <button onClick={() => {enableSnippet(snippet.name);}}>Enable</button>}
					</li>
				))}
			</ul>
			<Spacer marginBottom={6} />
		</>
	);
};
