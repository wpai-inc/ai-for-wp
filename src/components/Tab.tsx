import clsx from 'clsx';
import { useContext } from 'react';
import { TabProvider } from './Tabs';

export const Tab = ({ children, name = '' }) => {
	const { value, onChange } = useContext(TabProvider);
	return (
		<button
			className={clsx('cwpai-settings-tab', {
				'cwpai-settings-tab--active': value === name,
			})}
			aria-current="true"
			onClick={() => onChange(name)}
		>
			{children}
		</button>
	);
};
