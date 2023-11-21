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
            action: 'cwpai-helper/save-api-token',
            data: {
                token: project.token,
                autoSynchronize: project.auto_synchronize,
            },
            addNotification
        })
        setProject({...project, ...data});
    }

    const onAutoSynchronizeChanged = async (value: boolean) => {
        setProject({...project, auto_synchronize: value});
        if(project.token_placeholder) {
            const data = await cwpaiRequest({
                action: 'cwpai-helper/api-auto-synchronize-save',
                data: {
                    autoSynchronize: value
                },
                addNotification
            })
            setProject({...project, ...data});
        }
    };

    return (
        <Card>
            <CardHeader>
                <Heading>{__('API Key', 'cwpai-helper')}</Heading>
                <Button className="is-primary" variant="primary" target='_blank' href={pageProps.codewp_server + '/user/api-tokens'}>
                    {__('Get Your API key', 'cwpai-helper')}
                </Button>
            </CardHeader>
            <CardBody>
                <p>{__('Click the button above to get you API key. You can use this key to connect your website to a CodeWP project.', 'cwpai-helper')}</p>
                <p>{__('After you get your API key, add it on the field below and click save', 'cwpai-helper')}</p>
            </CardBody>
            <CardBody>
                <Disabled isDisabled={isSaving}>
                    <Flex align='end' className="cwpai-mb-1.5">
                        <FlexBlock>
                            <TextControl
                                label={__('CodeWP Project API Key', 'cwpai-helper')}
                                onChange={(value) => onTokenChanged(value)}
                                value={project.token}
                                disabled={isSaving}
                                placeholder={project.token_placeholder}
                                type="text"
                                className="cwpai-api-key-field"
                            />
                        </FlexBlock>
                        <Button className="is-primary cwpai-api-key-button" variant="primary" onClick={onClickSaveToken}
                                disabled={isSaving || !project.token}>
                            {__('Save', 'cwpai-helper')}
                        </Button>
                    </Flex>

                    {(project.token || project.token_placeholder) && (
                        <ToggleControl
                            label="Auto synchronize"
                            help={__('If enabled, the plugin will automatically synchronize your project with CodeWP.', 'cwpai-helper')}
                            checked={project.auto_synchronize}
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
                        <div>{__('Project', 'cwpai-helper')}: <a
                            href={pageProps.codewp_server + '/projects/' + project.project_id}
                            target="_blank"><strong>{project.project_name}</strong> <span
                            className="text-gray text-sm">({project.project_id})</span></a>
                        </div>
                    )}

                    {project.synchronized_at && (
                        <div>{__('Last synchronized', 'cwpai-helper')}: {project.synchronized_at}</div>
                    )}
                </Flex>
            </CardBody>
        </Card>
    );
};