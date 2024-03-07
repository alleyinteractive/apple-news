import { Button, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import React from 'react';

function PublishControls({
  apiAutosync,
  apiAutosyncDelete,
  apiAutosyncUpdate,
  deletePost,
  loading,
  postIsDirty,
  postStatus,
  publishPost,
  publishState,
  updatePost,
  userCanPublish,
}) {
  // If the post isn't published, or the user can't publish to Apple News, bail.
  if (postStatus !== 'publish' || !userCanPublish) {
    return null;
  }

  // If we're loading, spin.
  if (loading) {
    return <Spinner />;
  }

  return (
    <>
      {postIsDirty ? (
        <div className="components-notice is-warning">
          <strong>
            {__('Please click the Update button above to ensure that all changes are saved before publishing to Apple News.', 'apple-news')}
          </strong>
        </div>
      ) : null}
      {publishState !== 'N/A' && !apiAutosyncUpdate ? (
        <Button
          isPrimary
          onClick={updatePost}
          style={{ margin: '1em' }}
        >
          {__('Update', 'apple-news')}
        </Button>
      ) : null}
      {publishState !== 'N/A' && !apiAutosyncDelete ? (
        <Button
          isSecondary
          onClick={deletePost}
          style={{ margin: '1em' }}
        >
          {__('Delete', 'apple-news')}
        </Button>
      ) : null}
      {publishState === 'N/A' && !apiAutosync ? (
        <Button
          isPrimary
          onClick={publishPost}
          style={{ margin: '1em' }}
        >
          {__('Publish', 'apple-news')}
        </Button>
      ) : null}
    </>
  );
}

PublishControls.propTypes = {
  apiAutosync: PropTypes.bool.isRequired,
  apiAutosyncDelete: PropTypes.bool.isRequired,
  apiAutosyncUpdate: PropTypes.bool.isRequired,
  deletePost: PropTypes.func.isRequired,
  loading: PropTypes.bool.isRequired,
  postIsDirty: PropTypes.bool.isRequired,
  postStatus: PropTypes.string.isRequired,
  publishPost: PropTypes.func.isRequired,
  publishState: PropTypes.string.isRequired,
  updatePost: PropTypes.func.isRequired,
  userCanPublish: PropTypes.bool.isRequired,
};

export default PublishControls;
