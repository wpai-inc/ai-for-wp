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
import {__} from '@wordpress/i18n';
import {useContext, useState} from "react";
import {PagePropsContext} from "../../hooks/usePagePropsContext";
import {Project} from "../../types";
import cwpaiRequest from '../../utils/cwpaiRequest';
import {useNotificationsContext} from "../../hooks/useNotificationsContext";

export const TokenApiForm = () => {

    const pageProps = useContext(PagePropsContext);
    const [project, setProject] = useState<Project>(pageProps.project);
    const [isSaving, setIsSaving] = useState(false);
    const onTokenChanged = (value: string) => {
        setProject({...project, token: value});
    };

    const {addNotification} = useNotificationsContext();

    const onClickSaveToken = async () => {
        const data = await cwpaiRequest({
            action: 'cwpai-settings/api-auto-synchronize-save',
            data: {
                token: project.token,
                autoSynchronize: project.auto_syncronize,
            },
            addNotification
        })
        setProject({...project, ...data});
    }

    const onAutoSynchronizeChanged = async (value: boolean) => {
        setProject({...project, auto_syncronize: value});
        const data = await cwpaiRequest({
            action: 'cwpai-settings/api-auto-synchronize-save',
            data: {
                autoSynchronize: value
            },
            addNotification
        })
        setProject({...project, ...data});
    };

    return (
        <Card>
            <CardHeader>
                <Heading>{__('API Key', 'wp-cwpai-settings-page')}</Heading>
                <Button variant="primary" target='_blank' href={pageProps.codewp_server + '/user/api-tokens'}>
                    {__('Get your api key', 'wp-cwpai-settings-page')}
                </Button>
            </CardHeader>
            <CardBody>
                <p>{__('Click the button above to get you API key. You can use this API key to sync your project with CodeWP.', 'wp-cwpai-settings-page')}</p>
                <p>{__('After you get your API key, add it on the field below and click save', 'wp-cwpai-settings-page')}</p>
            </CardBody>
            <CardBody>
                <Disabled isDisabled={isSaving}>
                    <Flex align='end' className="cwpai-mb-1.5">
                        <FlexBlock>
                            <TextControl
                                label={__('CodeWP Project API Key', 'wp-cwpai-settings-page')}
                                onChange={(value) => onTokenChanged(value)}
                                value={project.token}
                                placeholder={project.token_placeholder}
                                type="text"
                            />
                        </FlexBlock>
                        <Button variant="primary" className="cwpai-mb-1.5" onClick={onClickSaveToken}
                                disabled={isSaving}>
                            {__('Save', 'wp-cwpai-settings-page')}
                        </Button>
                    </Flex>

                    {(project.token || project.token_placeholder) && (
                        <ToggleControl
                            label="Auto syncronize"
                            help={__('If enabled, the plugin will automatically synchronize your project with CodeWP.', 'wp-cwpai-settings-page')}
                            checked={project.auto_syncronize}
                            onChange={(value) => onAutoSynchronizeChanged(value)}
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
                    {project.project_id && project.project_name && (
                        <div>{__('Project', 'wp-cwpai-settings-page')}: <a
                            href={pageProps.codewp_server + '/projects/' + project.project_id}
                            target="_blank"><strong>{project.project_name}</strong> <span
                            className="text-gray text-sm">({project.project_id})</span></a>
                        </div>
                    )}

                    {project.synchronized_at && (
                        <div>{__('Last synchronized', 'wp-cwpai-settings-page')}: {project.synchronized_at}</div>
                    )}
                </Flex>
            </CardBody>
        </Card>
    );
};
