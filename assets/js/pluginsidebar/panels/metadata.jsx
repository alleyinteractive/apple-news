import {
  Button,
  CheckboxControl,
  PanelBody,
  SelectControl,
  TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import React from 'react';

// Config.
import { METADATA_SHAPE } from '../../config/prop-types';

// Util.
import deleteAtIndex from '../../util/delete-at-index';
import updateValueAtIndex from '../../util/update-value-at-index';

function Metadata({
  isHidden,
  isPaid,
  isPreview,
  isSponsored,
  metadata,
  onChangeIsHidden,
  onChangeIsPaid,
  onChangeIsPreview,
  onChangeIsSponsored,
  onChangeMetadata,
  onChangeSuppressVideoURL,
  onChangeUseImageComponent,
  suppressVideoURL,
  useImageComponent,
}) {
  return (
    <PanelBody
      initialOpen={false}
      title={__('Metadata', 'apple-news')}
    >
      <SelectControl
        help={__('Set this setting to true to indicate that viewing the article requires a paid subscription, false to indicate that it does not, or leave it empty to use the channel default value. Note that Apple must approve your channel for paid content before using this feature.', 'apple-news')}
        label={__('Paid Article', 'apple-news')}
        onChange={onChangeIsPaid}
        options={[
          { label: __('Channel Default', 'apple-news'), value: '' },
          { label: __('True', 'apple-news'), value: 'true' },
          { label: __('False', 'apple-news'), value: 'false' },
        ]}
        value={isPaid === '1' ? 'true' : isPaid}
      />
      <SelectControl
        help={__('Set this setting to true to publish the article as a draft.', 'apple-news')}
        label={__('Preview Article', 'apple-news')}
        onChange={onChangeIsPreview}
        options={[
          { label: __('Channel Default', 'apple-news'), value: '' },
          { label: __('True', 'apple-news'), value: 'true' },
          { label: __('False', 'apple-news'), value: 'false' },
        ]}
        value={isPreview === '1' ? 'true' : isPreview}
      />
      <SelectControl
        help={__('Set this setting to true to publish the article as a hidden article. Hidden articles are visible to users who have a link to the article, but do not appear in feeds.', 'apple-news')}
        label={__('Hidden Article', 'apple-news')}
        onChange={onChangeIsHidden}
        options={[
          { label: __('Channel Default', 'apple-news'), value: '' },
          { label: __('True', 'apple-news'), value: 'true' },
          { label: __('False', 'apple-news'), value: 'false' },
        ]}
        value={isHidden === '1' ? 'true' : isHidden}
      />
      <SelectControl
        help={__('Set this setting to true to indicate this article is sponsored content.', 'apple-news')}
        label={__('Sponsored Article', 'apple-news')}
        onChange={onChangeIsSponsored}
        options={[
          { label: __('Channel Default', 'apple-news'), value: '' },
          { label: __('True', 'apple-news'), value: 'true' },
          { label: __('False', 'apple-news'), value: 'false' },
        ]}
        value={isSponsored === '1' ? 'true' : isSponsored}
      />
      <CheckboxControl
        checked={suppressVideoURL}
        help={__('Check this to prevent video thumbnails for this article.', 'apple-news')}
        label={__('Do not set videoURL metadata for this article', 'apple-news')}
        onChange={onChangeSuppressVideoURL}
      />
      <CheckboxControl
        checked={useImageComponent}
        help={__('Check this to use an Image instead of a Photo component for images in this article.', 'apple-news')}
        label={__('Use Image component for images.', 'apple-news')}
        onChange={onChangeUseImageComponent}
      />
      <h3>{__('Custom Metadata', 'apple-news')}</h3>
      {metadata.map(({ key, type, value }, index) => (
      // eslint-disable-next-line react/no-array-index-key
        <div key={index}>
          <TextControl
            label={__('Key', 'apple-news')}
            onChange={(next) => onChangeMetadata(updateValueAtIndex(metadata, 'key', next, index))}
            value={key}
          />
          <SelectControl
            label={__('Type', 'apple-news')}
            onChange={(next) => onChangeMetadata(updateValueAtIndex(metadata, 'type', next, index))}
            options={[
              { label: __('string', 'apple-news'), value: 'string' },
              { label: __('boolean', 'apple-news'), value: 'boolean' },
              { label: __('number', 'apple-news'), value: 'number' },
              { label: __('array', 'apple-news'), value: 'array' },
            ]}
            value={type}
          />
          {type === 'boolean' ? (
            <SelectControl
              label={__('Value', 'apple-news')}
              onChange={(next) => onChangeMetadata(updateValueAtIndex(metadata, 'value', next === 'true', index))}
              options={[
                { label: __('', 'apple-news'), value: '' },
                { label: __('true', 'apple-news'), value: 'true' },
                { label: __('false', 'apple-news'), value: 'false' },
              ]}
              value={value}
            />
          ) : (
            <TextControl
              label={__('Value', 'apple-news')}
              onChange={(next) => onChangeMetadata(updateValueAtIndex(metadata, 'value', type === 'number' ? parseFloat(next) : next, index))}
              type={type === 'number' ? 'number' : 'text'}
              value={value}
            />
          )}
          <Button
            isDestructive
            onClick={() => onChangeMetadata(deleteAtIndex(metadata, index))}
            style={{ marginBottom: '1em' }}
          >
            {__('Remove', 'apple-news')}
          </Button>
        </div>
      ))}
      <Button
        isPrimary
        onClick={() => onChangeMetadata([...metadata, { key: '', type: 'string', value: '' }])}
      >
        {__('Add Metadata', 'apple-news')}
      </Button>
    </PanelBody>
  );
}

Metadata.propTypes = {
  isHidden: PropTypes.bool.isRequired,
  isPaid: PropTypes.bool.isRequired,
  isPreview: PropTypes.bool.isRequired,
  isSponsored: PropTypes.bool.isRequired,
  metadata: PropTypes.arrayOf(PropTypes.shape(METADATA_SHAPE)).isRequired,
  onChangeIsHidden: PropTypes.func.isRequired,
  onChangeIsPaid: PropTypes.func.isRequired,
  onChangeIsPreview: PropTypes.func.isRequired,
  onChangeIsSponsored: PropTypes.func.isRequired,
  onChangeMetadata: PropTypes.func.isRequired,
  onChangeSuppressVideoURL: PropTypes.func.isRequired,
  onChangeUseImageComponent: PropTypes.func.isRequired,
  suppressVideoURL: PropTypes.bool.isRequired,
  useImageComponent: PropTypes.bool.isRequired,
};

export default Metadata;
