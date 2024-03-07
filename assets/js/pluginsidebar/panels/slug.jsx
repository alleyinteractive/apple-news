import {
  PanelBody,
  TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import React from 'react';

function Slug({
  onChangeSlug,
  slug,
}) {
  return (
    <PanelBody
      initialOpen={false}
      title={__('Slug', 'apple-news')}
    >
      <TextControl
        help={__('A word or phrase that will appear near the title, if the Slug component is enabled in theme settings. This is optional and can be left blank.', 'apple-news')}
        label={__('Slug Text', 'apple-news')}
        onChange={onChangeSlug}
        value={slug}
      />
    </PanelBody>
  );
}

Slug.propTypes = {
  onChangeSlug: PropTypes.func.isRequired,
  slug: PropTypes.string.isRequired,
};

export default Slug;
