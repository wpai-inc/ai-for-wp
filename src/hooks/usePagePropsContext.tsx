import {createContext, useContext} from "react";

export const PagePropsContext = createContext(undefined);

export function usePagePropsContext() {
    const pageProps = useContext(PagePropsContext);
    if (pageProps === undefined) {
        throw new Error('usePageProps must be used within a PagePropsProvider');
    }
    return pageProps;
}