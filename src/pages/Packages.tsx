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
    Flex, FlexItem, Notice,
} from '@wordpress/components';
import {__, sprintf} from '@wordpress/i18n';
import CwpGuide from '../components/CwpGuide';
import React, {useContext, useEffect, useRef, useState} from 'react';
import {LightAsync as SyntaxHighlighter} from 'react-syntax-highlighter';
import {atomOneDark} from 'react-syntax-highlighter/dist/esm/styles/hljs';
import {PagePropsContext} from '../hooks/usePagePropsContext';
import {useNotificationsContext} from '../hooks/useNotificationsContext';
import codewpaiRequest from "../utils/codewpaiRequest";
import {SnippetType, PackageType, PackageFileType, type SettingsType} from "../types";

import IconPHP from '../svgs/php.svg?react';
import IconJs from '../svgs/js.svg?react';
import IconCss from '../svgs/css.svg?react';

declare const ajaxurl: string;
declare const CODEWPAI_SETTINGS: SettingsType;

type LoadingPackageType = {
    package: string,
    action: string,
    snippet: string | null
}

export const Packages = () => {
    const [previewingCode, setPreviewingCode] = useState(false);
    const [previewSnippet, setPreviewSnippet] = useState<SnippetType | null>(null);
    const [allPackages, setAllPackages] = useState<PackageType[] | null>(null);
    const [loadingPackages, setLoadingPackages] = useState(false);
    const [loadingPackage, setLoadingPackage] = useState<LoadingPackageType | null>(null);
    const [previewSnippetCode, setPreviewSnippetCode] = useState({code: ''});
    const pageProps = useContext(PagePropsContext);
    const guideRef = useRef(null);
    const {addNotification} = useNotificationsContext();
    const enabledNotification = (snippet_name) =>
        __(`Snippet ${snippet_name} has been enabled`, 'codewpai');
    const disabledNotification = (snippet_name) =>
        __(`Snippet ${snippet_name} has been disabled`, 'codewpai');
    const errorNotification = (snippet_name) =>
        __(`Snippet ${snippet_name} has been disabled because it trows a fatal error`, 'codewpai');


    async function fetchAllPackages(force = false) {
        setLoadingPackages(true);
        const data = await codewpaiRequest({
            action: 'codewpai_packages',
            method: 'GET',
            data: {
                'force_update': force ? 1 : 0
            },
            addNotification
        })

        console.log(data)
        if (data) {
            setAllPackages(data);
        }

        setLoadingPackages(false);
    }

    async function installPackage(package_id: string) {
        setLoadingPackage({
            package: package_id,
            action: 'install',
            snippet: null
        });
        const data = await codewpaiRequest({
            action: 'codewpai_install_package',
            method: 'get',
            data: {
                'package_id': package_id
            },
            addNotification
        })
        if (data) {
            setAllPackages(data);
            addNotification(__('Package installed', 'codewpai'), 'default');
        }
        setLoadingPackage(null);
    }

    async function updatePackage(package_id: string) {
        setLoadingPackage({
            package: package_id,
            action: 'update',
            snippet: null
        });
        const data = await codewpaiRequest({
            action: 'codewpai_update_package',
            method: 'get',
            data: {
                'package_id': package_id
            },
            addNotification
        })
        if (data) {
            setAllPackages(data);
            addNotification(__('Package updated', 'codewpai'), 'default');
        }
        setLoadingPackage(null);
    }

    async function uninstallPackage(thePackage: PackageType) {
        if (!confirm(sprintf(__('Are you sure you want to uninstall this %s?', 'codewpai'), thePackage.type === 0 ? 'package' : 'plugin'))) {
            return;
        }
        setLoadingPackage({
            package: thePackage.id,
            action: 'install',
            snippet: null
        });
        const data = await codewpaiRequest({
            action: 'codewpai_uninstall_package',
            method: 'get',
            data: {
                'package_id': thePackage.id
            },
            addNotification
        })
        if (data) {
            setAllPackages(data);
            addNotification(__('Package uninstalled', 'codewpai'), 'info');
        }
        setLoadingPackage(null);
    }

    async function toggleAllSnippets(thePackage: PackageType, enabled: number) {
        setLoadingPackage({
            package: thePackage.id,
            action: 'enable',
            snippet: null
        });
        const data = await codewpaiRequest({
            action: 'codewpai_toggle_all_snippets',
            method: 'get',
            data: {
                'package_id': thePackage.id,
                'enabled': enabled
            },
            addNotification
        })

        if (data) {
            setAllPackages(data);

            if(thePackage.type === 0){
                if(enabled){
                    addNotification(__('All package snippets have been enabled', 'codewpai'), 'default');
                }else{
                    addNotification(__('All package snippets have been disabled', 'codewpai'), 'info');
                }
            }else{
                if(enabled){
                    addNotification(__('Plugin has been enabled', 'codewpai'), 'default');
                }else{
                    addNotification(__('Plugin has been disabled', 'codewpai'), 'info');
                }
            }
        }
        setLoadingPackage(null);
    }

    async function toggleSnippet(package_id: string, file: PackageFileType) {
        setLoadingPackage({
            package: package_id,
            action: 'enable',
            snippet: file.id
        });
        const data = await codewpaiRequest({
            action: 'codewpai_toggle_snippet',
            method: 'get',
            data: {
                'package_id': package_id,
                'snippet_id': file.id,
                'enabled': file.enabled ? 0 : 1
            },
            addNotification
        })
        if (data) {
            setAllPackages(data);
            addNotification(sprintf(__('Snippet has been %s'), file.enabled ? 'disabled' : 'enabled'), file.enabled ? 'info' : 'default');
        }
        setLoadingPackage(null);
    }

    async function handlePreview(package_id, file) {
        setPreviewingCode(true);
        setPreviewSnippet(file);
        const data = await codewpaiRequest({
            action: 'codewpai_get_snippet_code',
            method: 'get',
            data: {
                'package_id': package_id,
                'snippet_id': file.id,
            },
            addNotification
        })
        if (data) {
            setPreviewSnippetCode(data);
        }
    }

    function closePreview() {
        setPreviewingCode(false);
        setPreviewSnippetCode({code: ''});
        setPreviewSnippet(null);
    }

    useEffect(() => {
        fetchAllPackages();

        // extract query strings from window.location.search
        const urlParams = new URLSearchParams(window.location.search);
        const snippetEnabled = urlParams.get('snippet_enabled');

        if (snippetEnabled) {
            addNotification(enabledNotification(snippetEnabled), 'default');
            urlParams.delete('snippet_enabled');
            window.history.pushState({}, '', `${window.location.pathname}?${urlParams}`);
        }
        const snippetDisabled = urlParams.get('snippet_disabled');
        if (snippetDisabled) {
            addNotification(disabledNotification(snippetDisabled), 'default');
            urlParams.delete('snippet_disabled');
            window.history.pushState({}, '', `${window.location.pathname}?${urlParams}`);
        }
        const snippetError = urlParams.get('snippet_error');
        if (snippetError) {
            addNotification(errorNotification(snippetError), 'error');
            urlParams.delete('snippet_error');
            window.history.pushState({}, '', `${window.location.pathname}?${urlParams}`);
        }
    }, []);

    return (
        <>
            <Spacer marginBottom={6}/>
            <Card>
                <CardHeader>
                    <Flex align="center" justify='space-between' gap={2}>
                        <Heading>
                            {__('Packages', 'codewpai')}
                        </Heading>
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
                                style={{cursor: 'pointer'}}
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
                        {!pageProps.playground_mode &&
                            <Button
                                variant='secondary'
                                isBusy={loadingPackages}
                                onClick={() => fetchAllPackages(true)}
                            >
                                {__('Check for updates')}
                            </Button>
                        }
                    </Flex>
                </CardHeader>
                <CardBody>

                    {loadingPackages && !allPackages &&
                        <Text>{__('Loading all packages from codewp.ai. Please wait...', 'codewpai')}</Text>}
                    {allPackages && allPackages.length > 0 && (
                        <Flex direction='column' gap='8'>
                            {allPackages.map((thePackage) => (
                                <FlexItem>
                                    <Flex justify="space-between">
                                        <FlexItem>
                                            <Flex justify='start' align="start">
                                                <FlexItem>
                                                    <div style={{
                                                        fontSize: '16px',
                                                        fontWeight: 'bold'
                                                    }}>{thePackage.name}</div>
                                                </FlexItem>
                                                <FlexItem>
                                                    <span
                                                        className='codewpai-badge'>{thePackage.type === 0 ? 'Package' : 'Plugin'}</span>
                                                </FlexItem>
                                            </Flex>
                                        </FlexItem>
                                        <FlexItem>
                                            <Flex justify='start'>
                                                {!pageProps.playground_mode && !thePackage.installed &&
                                                    <FlexItem>
                                                        <Button
                                                            variant="primary"
                                                            size="small"
                                                            onClick={() => {
                                                                installPackage(thePackage.id)
                                                            }}
                                                            disabled={loadingPackage && loadingPackage.package === thePackage.id}
                                                            isBusy={loadingPackage && loadingPackage.package === thePackage.id && loadingPackage.action === 'install' && loadingPackage.snippet === null}
                                                        >
                                                            Install {thePackage.type === 0 ? 'Package' : 'Plugin'}
                                                        </Button>
                                                    </FlexItem>
                                                }

                                                {thePackage.installed &&
                                                    <FlexItem>
                                                        <Button
                                                            variant="primary"
                                                            size="small"
                                                            label={thePackage.has_enabled_snippets ? __('Disable all snippets in this package', 'codewpai') : __('Enable all snippets in this package', 'codewpai')}
                                                            showTooltip={true}
                                                            onClick={() => toggleAllSnippets(thePackage, thePackage.has_enabled_snippets ? 0 : 1)}
                                                            disabled={loadingPackage && loadingPackage.package === thePackage.id}
                                                            isBusy={loadingPackage && loadingPackage.package === thePackage.id && loadingPackage.action === 'enable' && loadingPackage.snippet === null}
                                                        >
                                                            { thePackage.type === 0 &&
                                                                (thePackage.has_enabled_snippets ? __('Disable all', 'codewpai') : __('Enable all', 'codewpai'))
                                                            }
                                                            { thePackage.type === 1 &&
                                                                (thePackage.has_enabled_snippets ? __('Disable plugin', 'codewpai') : __('Enable plugin', 'codewpai'))
                                                            }
                                                        </Button>
                                                    </FlexItem>
                                                }
                                                {!pageProps.playground_mode && thePackage.installed && thePackage.update_available &&
                                                    <FlexItem>
                                                        <Button
                                                            variant="primary"
                                                            size="small"
                                                            onClick={() => {
                                                                updatePackage(thePackage.id)
                                                            }}
                                                            disabled={loadingPackage && loadingPackage.package === thePackage.id}
                                                            isBusy={loadingPackage && loadingPackage.package === thePackage.id && loadingPackage.action === 'update'}
                                                        >
                                                            Update {thePackage.type === 0 ? 'Package' : 'Plugin'}
                                                        </Button>
                                                    </FlexItem>}
                                                {!pageProps.playground_mode && thePackage.installed &&
                                                    <FlexItem>
                                                        <Button
                                                            variant="secondary"
                                                            isDestructive={true}
                                                            size="small"
                                                            onClick={() => {
                                                                uninstallPackage(thePackage)
                                                            }}
                                                            disabled={loadingPackage && loadingPackage.package === thePackage.id}
                                                            isBusy={loadingPackage && loadingPackage.package === thePackage.id && loadingPackage.action === 'install' && loadingPackage.snippet === null}
                                                        >
                                                            Uninstall {thePackage.type === 0 ? 'Package' : 'Plugin'}
                                                        </Button>
                                                    </FlexItem>
                                                }
                                                {!pageProps.playground_mode &&
                                                    <FlexItem>
                                                        <Button
                                                            variant="secondary"
                                                            size="small"
                                                            href={CODEWPAI_SETTINGS.codewp_server + '/packages/' + thePackage.id}
                                                            target='_blank'
                                                        >
                                                            {__('Edit', 'codewpai')}
                                                        </Button>
                                                    </FlexItem>
                                                }
                                            </Flex>
                                        </FlexItem>
                                    </Flex>
                                    <table className="wp-list-table widefat fixed" style={{marginTop: '10px'}}>
                                        <thead>
                                        <tr>
                                            <th>{__('File', 'codewpai')}</th>
                                            <th>{__('Description', 'codewpai')}</th>
                                            {thePackage.type === 0 &&
                                                <>
                                                    <th style={{
                                                        textAlign: 'right',
                                                        width: '190px'
                                                    }}>{__('Actions', 'codewpai')}</th>
                                                </>
                                            }
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {thePackage.files.map((file) => (
                                            <>
                                                <tr key={file.id} className={
                                                    (file.enabled ? 'snippet_enabled ' : 'snippet_disabled ')
                                                    + (!thePackage.installed ? 'snippet_not_installed ' : '')
                                                }>
                                                    <td>
                                                        {file.extension === 'php' &&
                                                            <img src={IconPHP} alt="PHP" style={{
                                                                width: '28px',
                                                                verticalAlign: 'middle',
                                                                marginRight: '6px'
                                                            }}/>}
                                                        {file.extension === 'js' && <img src={IconJs} alt="PHP" style={{
                                                            width: '28px',
                                                            verticalAlign: 'middle',
                                                            marginRight: '6px'
                                                        }}/>}
                                                        {file.extension === 'css' &&
                                                            <img src={IconCss} alt="PHP" style={{
                                                                width: '28px',
                                                                verticalAlign: 'middle',
                                                                marginRight: '6px'
                                                            }}/>}
                                                        {file.name}
                                                    </td>
                                                    <td>{file.description}</td>
                                                    {thePackage.type === 0 &&
                                                        <>
                                                            <td style={{textAlign: 'right'}}>
                                                                {thePackage.installed &&
                                                                    <Flex>
                                                                        <FlexItem>
                                                                            <ToggleControl
                                                                                label={file.enabled ? __('Enabled', 'codewpai') : __('Disabled', 'codewpai')}
                                                                                checked={file.enabled}
                                                                                onChange={() =>
                                                                                    toggleSnippet(thePackage.id, file)
                                                                                }
                                                                                disabled={(loadingPackage && loadingPackage.package === thePackage.id)}
                                                                                className="codewpai-mb-0"
                                                                            />
                                                                        </FlexItem>
                                                                        <FlexItem>
                                                                            <Button
                                                                                variant="secondary"
                                                                                onClick={() => handlePreview(thePackage.id, file)}
                                                                            >{__('Preview')}</Button>
                                                                        </FlexItem>
                                                                    </Flex>
                                                                }
                                                                {!thePackage.installed &&
                                                                    <div>{__('Not Installed', 'codewpai')}</div>}
                                                            </td>
                                                        </>
                                                    }
                                                </tr>
                                                {file.error && <tr>
                                                    <td colSpan={thePackage.type === 0 ? 3 : 2}>
                                                        <Notice status='error'
                                                                isDismissible={false}>
                                                            <Flex>
                                                                <FlexItem>
                                                                    {file.error.message}
                                                                </FlexItem>
                                                                {!pageProps.playground_mode &&
                                                                    <FlexItem>
                                                                        <Button
                                                                            variant="secondary"
                                                                            size="small"
                                                                            href={CODEWPAI_SETTINGS.codewp_server + '/debug/' + file.id + '?error=' + encodeURI(file.error.message) + '&line=' + file.error.line}
                                                                            target='_blank'
                                                                            // onClick={() => handlePreview(thePackage.id, file)}
                                                                        >
                                                                            {__('Debug on Codewp.ai', 'codewpai')}
                                                                        </Button>
                                                                    </FlexItem>
                                                                }
                                                            </Flex>
                                                        </Notice>
                                                    </td>
                                                </tr>}
                                            </>
                                        ))}
                                        </tbody>
                                    </table>

                                </FlexItem>
                            ))}
                        </Flex>
                    )}
                </CardBody>
            </Card>
            <CwpGuide ref={guideRef}/>
            <Spacer marginBottom={6}/>

            {previewingCode && previewSnippet && (
                <Modal
                    title={previewSnippet?.name}
                    className="codewpai-snippet-preview"
                    onRequestClose={closePreview}
                >
                    <SyntaxHighlighter language="php" style={atomOneDark}>
                        {previewSnippetCode.code}
                    </SyntaxHighlighter>
                </Modal>
            )}
        </>
    );
};
