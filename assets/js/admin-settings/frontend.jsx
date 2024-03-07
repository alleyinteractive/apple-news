import React, { StrictMode } from 'react';
import ReactDOM from 'react-dom';

// Components.
import AdminSettings from './index';

const container = document.getElementById('apple-news-options__page');
const root = ReactDOM.createRoot(container);

root.render(
  <StrictMode>
    <AdminSettings />
  </StrictMode>,
);
