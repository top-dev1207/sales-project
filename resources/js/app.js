require('./bootstrap');
require('alpinejs');

import React from 'react';
import ReactDOM from 'react-dom';
import Widget from './components/widget';

const root = document.getElementById('react-widget');

if (root) {
    ReactDOM.render(<Widget />, root);
    console.log("Widget rendered successfully");
} else {
    console.warn("Element with ID 'react-widget' not found");
}