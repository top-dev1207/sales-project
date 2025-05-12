require('./bootstrap');
require('alpinejs');

import React from 'react';
import ReactDOM from 'react-dom/client';
import Widget from './components/Widget';

const rootEl = document.getElementById('resumenVer');

if (rootEl) {
  const root = ReactDOM.createRoot(rootEl);
  root.render(<Widget />);
}
