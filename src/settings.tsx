import React from 'react';
import {render} from 'react-dom';
import App from './App';

const wpaiElement = document.getElementById('codewpai-ui-settings');
document.getElementById('codewpai-ui-loading')?.remove();
render(<App/>, wpaiElement);
