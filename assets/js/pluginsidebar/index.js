/* global React, wp */

import Icon from './components/icon';
import Sidebar from './components/sidebar';

if ('undefined' !== typeof wp) {
  const {
    plugins: {
      registerPlugin = null,
    } = {},
  } = wp;

  if ('function' === typeof registerPlugin) {
    registerPlugin('publish-to-apple-news', {
      icon: <Icon />,
      render: Sidebar,
    });
  }
}
