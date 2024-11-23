import apiFetch from '@wordpress/api-fetch';
import { ImagePicker, MediaPicker } from '@alleyinteractive/block-editor-tools';
import {
  BaseControl,
  PanelBody,
  RadioControl,
  TextareaControl,
  TextControl,
  Notice,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import PropTypes from 'prop-types';
import React, { useState, useEffect } from 'react';

function CoverMedia({
  coverMediaProvider,
  coverImageCaption,
  coverImageId,
  coverVideoId,
  coverVideoUrl,
  coverEmbedWebVideoUrl,
  onChangeCoverMediaProvider,
  onChangeCoverImageCaption,
  onChangeCoverImageId,
  onChangeCoverVideoId,
  onChangeCoverVideoUrl,
  onChangeCoverEmbedWebVideoUrl,
}) {
  const [isValidCoverMedia, setIsValidCoverMedia] = useState(true);

  useEffect(() => {
    const endpoint = '/apple-news/v1/is-valid-cover-media';
    const type = coverMediaProvider;
    let url = '';

    if (coverMediaProvider === 'video_url') {
      url = coverVideoUrl;
    }

    if (coverMediaProvider === 'embedwebvideo') {
      url = coverEmbedWebVideoUrl;
    }

    if (url) {
      const apiPath = addQueryArgs(endpoint, { type, url });
      const apiReq = apiFetch({ path: apiPath });

      apiReq.then((response) => {
        setIsValidCoverMedia(response.isValidCoverMedia);
      });
    }
  }, [coverMediaProvider, coverVideoUrl, coverEmbedWebVideoUrl]);

  return (
    <PanelBody
      initialOpen={false}
      title={__('Cover Media', 'apple-news')}
    >
      <RadioControl
        label={__('Source', 'apple-news')}
        onChange={onChangeCoverMediaProvider}
        options={[
          {
            label: __('Uploaded Image', 'apple-news'),
            value: 'image',
          },
          {
            label: __('Uploaded MP4 Video', 'apple-news'),
            value: 'video_id',
          },
          {
            label: __('External MP4 Video', 'apple-news'),
            value: 'video_url',
          },
          {
            label: __('Embedded Video (YouTube, etc.)', 'apple-news'),
            value: 'embedwebvideo',
          },
        ]}
        selected={coverMediaProvider}
      />

      {coverMediaProvider === 'image' ? (
        <>
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
        </>
      ) : null}

      {coverMediaProvider === 'video_id' ? (
        <MediaPicker
          allowedTypes={['video/mp4']}
          icon="format-video"
          onReset={() => onChangeCoverVideoId(0)}
          onUpdate={({ id }) => onChangeCoverVideoId(id)}
          value={coverVideoId}
          preview={({ src }) => ( // eslint-disable-line react/no-unstable-nested-components
            <video controls src={src} /> // eslint-disable-line jsx-a11y/media-has-caption
          )}
        />
      ) : null}

      {coverMediaProvider === 'video_url' ? (
        <>
          <TextControl
            label={__('Video URL', 'apple-news')}
            help={__('Enter an MP4 video URL.', 'apple-news')}
            value={coverVideoUrl}
            onChange={onChangeCoverVideoUrl}
            type="url"
          />

          {!isValidCoverMedia ? (
            <Notice
              status="error"
              isDismissible={false}
            >
              {__('This URL is not supported. Only MP4 video URLs are supported.', 'apple-news')}
            </Notice>
          ) : null}
        </>
      ) : null}

      {coverMediaProvider === 'embedwebvideo' ? (
        <>
          <TextControl
            label={__('Video URL', 'apple-news')}
            help={__('Enter a YouTube, Vimeo, or Dailymotion video URL.', 'apple-news')}
            value={coverEmbedWebVideoUrl}
            onChange={onChangeCoverEmbedWebVideoUrl}
            type="url"
          />

          {!isValidCoverMedia ? (
            <Notice
              status="error"
              isDismissible={false}
            >
              {__('This URL is not supported. Only videos from YouTube, Vimeo, or Dailymotion are supported.', 'apple-news')}
            </Notice>
          ) : null}
        </>
      ) : null}
    </PanelBody>
  );
}

CoverMedia.propTypes = {
  coverMediaProvider: PropTypes.string.isRequired,
  coverImageCaption: PropTypes.string.isRequired,
  coverImageId: PropTypes.number.isRequired,
  coverVideoId: PropTypes.number.isRequired,
  coverVideoUrl: PropTypes.string.isRequired,
  coverEmbedWebVideoUrl: PropTypes.string.isRequired,
  onChangeCoverMediaProvider: PropTypes.func.isRequired,
  onChangeCoverImageCaption: PropTypes.func.isRequired,
  onChangeCoverImageId: PropTypes.func.isRequired,
  onChangeCoverVideoId: PropTypes.func.isRequired,
  onChangeCoverVideoUrl: PropTypes.func.isRequired,
  onChangeCoverEmbedWebVideoUrl: PropTypes.func.isRequired,
};

export default CoverMedia;
