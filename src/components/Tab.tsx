import {useContext} from 'react';
import {TabProvider} from './Tabs';

export const Tab = ({children, name = ''}) => {
    const {value, onChange} = useContext(TabProvider);
    const isActive = value === name;
    return (
        <button
            className={'cwpai-helper-tab ' + (isActive ? 'cwpai-helper-tab--active' : '')}
            aria-current="true"
            onClick={() => onChange(name)}
        >
            {children}
        </button>
    );
};
