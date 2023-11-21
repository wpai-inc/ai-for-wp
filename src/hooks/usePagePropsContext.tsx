import {createContext, useContext} from "react";
import {CwpaiSettings} from "../types";

export const PagePropsContext = createContext<CwpaiSettings | undefined>(undefined);

export function usePagePropsContext() {
    const pageProps = useContext(PagePropsContext);
    if (pageProps === undefined) {
        throw new Error('usePageProps must be used within a PagePropsProvider');
    }
    return pageProps;
}