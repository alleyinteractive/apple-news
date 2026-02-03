import { PanelBody, RadioControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import React from 'react';

function ChannelSelector({
  channel,
  multiChannelEnabled,
  onChangeChannel,
  primaryPublishState,
  secondaryChannelConfigured,
  secondaryPublishState,
}) {
  // Only show if multi-channel is enabled and secondary channel is configured.
  if (!multiChannelEnabled || !secondaryChannelConfigured) {
    return null;
  }

  const getStatusLabel = (state) => {
    if (!state || state === 'N/A') {
      return __('Not Published', 'apple-news');
    }
    return state;
  };

  return (
    <PanelBody
      initialOpen
      title={__('Channel Selection', 'apple-news')}
    >
      <RadioControl
        label={__('Publish to Channel', 'apple-news')}
        help={__('Select which Apple News channel to publish this article to.', 'apple-news')}
        selected={channel || 'primary'}
        options={[
          {
            label: `${__('Primary Channel (New York Post)', 'apple-news')} (${getStatusLabel(primaryPublishState)})`,
            value: 'primary',
          },
          {
            label: `${__('Secondary Channel (California Post)', 'apple-news')} (${getStatusLabel(secondaryPublishState)})`,
            value: 'secondary',
          },
        ]}
        onChange={onChangeChannel}
      />
    </PanelBody>
  );
}

ChannelSelector.propTypes = {
  channel: PropTypes.string.isRequired,
  multiChannelEnabled: PropTypes.bool.isRequired,
  onChangeChannel: PropTypes.func.isRequired,
  primaryPublishState: PropTypes.string,
  secondaryChannelConfigured: PropTypes.bool.isRequired,
  secondaryPublishState: PropTypes.string,
};

ChannelSelector.defaultProps = {
  primaryPublishState: 'N/A',
  secondaryPublishState: 'N/A',
};

export default ChannelSelector;
