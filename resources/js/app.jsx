import React from 'react';
import ReactDOM from 'react-dom';
import Widget from './components/Widget';

const root = document.getElementById('react-widget');
if (root) {
    ReactDOM.render(<Widget />, root);
}
