import {
    __experimentalHeading as Heading,
    Button,
    Card,
    CardBody,
    CardHeader,
    Disabled,
    Flex,
    FlexBlock,
    Spinner,
    TextControl,
    ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useContext, useState } from "react";
import { PagePropsContext } from "../../hooks/usePagePropsContext";
import { Project } from "../../types";
import codewpaiRequest from '../../utils/codewpaiRequest';
import { useNotificationsContext } from "../../hooks/useNotificationsContext";

export const TokenApiForm = () => {

    const pageProps = useContext(PagePropsContext);
    const [project, setProject] = useState<Project>(pageProps.project);
    const [isSaving, setIsSaving] = useState(false);
    const onTokenChanged = (value: string) => {
        setProject({ ...project, token: value });
    };

    const { addNotification } = useNotificationsContext();

    const onClickSaveToken = async () => {
        const data = await codewpaiRequest({
            action: 'codewpai_save_api_token',
            data: {
                token: project.token,
                autoSynchronize: project.auto_synchronize,
            },
            addNotification
        })
        setProject({ ...project, ...data });
    }

    const onAutoSynchronizeChanged = async (value: boolean) => {
        setProject({ ...project, auto_synchronize: value });
        if (project.token_placeholder) {
            const data = await codewpaiRequest({
                action: 'codewpai_api_auto_synchronize_save',
                data: {
                    autoSynchronize: value
                },
                addNotification
            })
            setProject({ ...project, ...data });
        }
    };

    return (
        <Card>
            <CardHeader>
                <Heading>{__('API Key', 'ai-for-wp')}</Heading>
                <Button
                    className="is-primary"
                    variant="primary"
                    target="_blank"
                    href={pageProps.codewp_server + '/user/api-tokens'}
                >
                    {__('Get Your API key', 'ai-for-wp')}
                </Button>
            </CardHeader>
            <CardBody>
                <p>
                    {__(
                        'Click the button above to get you API key. You can use this key to connect your website to a CodeWP project.',
                        'ai-for-wp'
                    )}
                </p>
                <p>
                    {__(
                        'After you get your API key, add it on the field below and click save',
                        'ai-for-wp'
                    )}
                </p>
            </CardBody>
            <CardBody>
                <Disabled isDisabled={isSaving}>
                    <Flex align="end" className="codewpai-mb-1.5">
                        <FlexBlock>
                            <TextControl
                                label={__('CodeWP Project API Key', 'ai-for-wp')}
                                onChange={(value) => onTokenChanged(value)}
                                value={project.token}
                                disabled={isSaving}
                                placeholder={project.token_placeholder}
                                type="text"
                                className="codewpai-api-key-field"
                            />
                        </FlexBlock>
                        <Button
                            className="is-primary codewpai-api-key-button"
                            variant="primary"
                            onClick={onClickSaveToken}
                            disabled={isSaving || !project.token}
                        >
                            {__('Save', 'ai-for-wp')}
                        </Button>
                    </Flex>

                    {(project.token || project.token_placeholder) && (
                        <ToggleControl
                            label="Auto synchronize"
                            help={__(
                                'If enabled, the plugin will automatically synchronize your project with CodeWP.',
                                'ai-for-wp'
                            )}
                            checked={project.auto_synchronize}
                            onChange={(value) => onAutoSynchronizeChanged(value)}
                        />
                    )}

                    {isSaving && (
                        <div className="spinner-wrap-overlay">
                            <Spinner />
                        </div>
                    )}
                </Disabled>

                <hr />

                <Flex direction="column" gap="2">
                    {project.project_id && project.project_name && (
                        <div>
                            {__('Project', 'ai-for-wp')}:{' '}
                            <a
                                href={pageProps.codewp_server + '/sites/' + project.project_id}
                                target="_blank"
                            >
                                <strong>{project.project_name}</strong>{' '}
                                <span className="text-gray text-sm">({project.project_id})</span>
                            </a>
                        </div>
                    )}

                    {project.synchronized_at && (
                        <div>
                            {__('Last synchronized', 'ai-for-wp')}: {project.synchronized_at}
                        </div>
                    )}
                </Flex>
            </CardBody>
        </Card>
    );
};
