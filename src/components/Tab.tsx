import {useContext} from 'react';
import {TabProvider} from './Tabs';

export const Tab = ({children, name = ''}) => {
    const {value, onChange} = useContext(TabProvider);
    const isActive = value === name;
    return (
        <button
            className={'cwpai-settings-tab ' + (isActive ? 'cwpai-settings-tab--active' : '')}
            aria-current="true"
            onClick={() => onChange(name)}
        >
            {children}
        </button>
    );
};
