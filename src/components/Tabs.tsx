import {createContext} from 'react';

export const TabProvider = createContext({
    value: '',
    onChange: (name: string) => {
    },
});

export const Tabs = ({children, value, onChange}) => {
    return (
        <nav className="cwpai-helper-tabs-wrapper hide-if-no-js">
            <TabProvider.Provider value={{value, onChange}}>{children}</TabProvider.Provider>
        </nav>
    );
};
