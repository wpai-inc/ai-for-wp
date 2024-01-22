import {createContext, useContext} from "react";
import {Settings} from "../types";

export const PagePropsContext = createContext<Settings | undefined>(undefined);

export function usePagePropsContext() {
    const pageProps = useContext(PagePropsContext);
    if (pageProps === undefined) {
        throw new Error('usePageProps must be used within a PagePropsProvider');
    }
    return pageProps;
}