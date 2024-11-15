import React, { StrictMode } from 'react';
import ReactDOM from 'react-dom';
import Mappings from './mappings';

const container = document.getElementById('apple-news-options__section-mappings');
const root = ReactDOM.createRoot(container);

root.render(
  <StrictMode>
    <Mappings />
  </StrictMode>,
);
