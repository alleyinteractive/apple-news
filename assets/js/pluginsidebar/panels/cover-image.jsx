import { ImagePicker } from '@alleyinteractive/block-editor-tools';
import { BaseControl, PanelBody, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import React from 'react';

function CoverImage({
  coverImageCaption,
  coverImageId,
  onChangeCoverImageCaption,
  onChangeCoverImageId,
}) {
  return (
    <PanelBody
      initialOpen={false}
      title={__('Cover Image', 'apple-news')}
    >
      <BaseControl>
        <ImagePicker
          onReset={() => onChangeCoverImageId(0)}
          onUpdate={({ id }) => onChangeCoverImageId(id)}
          value={coverImageId}
        />
      </BaseControl>
      <TextareaControl
        help={__('This is optional and can be left blank.', 'apple-news')}
        label={__('Caption', 'apple-news')}
        onChange={onChangeCoverImageCaption}
        placeholder={__('Add an image caption here.', 'apple-news')}
        value={coverImageCaption}
      />
    </PanelBody>
  );
}

CoverImage.propTypes = {
  coverImageCaption: PropTypes.string.isRequired,
  coverImageId: PropTypes.number.isRequired,
  onChangeCoverImageCaption: PropTypes.func.isRequired,
  onChangeCoverImageId: PropTypes.func.isRequired,
};

export default CoverImage;
