import {render} from 'react-dom';
import App from './App';

const wpaiElement = document.getElementById('cwpai-ui-settings');
document.getElementById('cwpai-ui-loading')?.remove();
render(<App/>, wpaiElement);
