import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import React from 'react';

function PublishInfo({
  apiId,
  dateCreated,
  dateModified,
  revision,
  shareUrl,
  publishState,
}) {
  if (!publishState || publishState === 'N/A') {
    return null;
  }

  return (
    <PanelBody
      initialOpen={false}
      title={__('Publish Information', 'apple-news')}
    >
      <h3>{__('API Id', 'apple-news')}</h3>
      <p>{apiId}</p>
      <h3>{__('Created On', 'apple-news')}</h3>
      <p>{dateCreated}</p>
      <h3>{__('Last Updated On', 'apple-news')}</h3>
      <p>{dateModified}</p>
      <h3>{__('Share URL', 'apple-news')}</h3>
      <p>{shareUrl}</p>
      <h3>{__('Revision', 'apple-news')}</h3>
      <p>{revision}</p>
      <h3>{__('Publish State', 'apple-news')}</h3>
      <p>{publishState}</p>
    </PanelBody>
  );
}

PublishInfo.propTypes = {
  apiId: PropTypes.string.isRequired,
  dateCreated: PropTypes.string.isRequired,
  dateModified: PropTypes.string.isRequired,
  revision: PropTypes.string.isRequired,
  shareUrl: PropTypes.string.isRequired,
  publishState: PropTypes.string.isRequired,
};

export default PublishInfo;
