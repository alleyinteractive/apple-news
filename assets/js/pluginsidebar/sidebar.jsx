import { usePostMeta, usePostMetaValue } from '@alleyinteractive/block-editor-tools';
import apiFetch from '@wordpress/api-fetch';
import { useDispatch, useSelect } from '@wordpress/data';
import {
  PluginSidebar,
  PluginSidebarMoreMenuItem,
} from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import DOMPurify from 'dompurify';
import React, { useCallback, useEffect, useState } from 'react';

// Panels.
import CoverImage from './panels/cover-image';
import MaturityRating from './panels/maturity-rating';
import Metadata from './panels/metadata';
import PublishControls from './panels/publish-controls';
import PublishInfo from './panels/publish-info';
import PullQuote from './panels/pull-quote';
import Sections from './panels/sections';
import Slug from './panels/slug';

// Utils.
import safeJsonParseArray from '../util/safe-json-parse-array';

function Sidebar() {
  const [state, setState] = useState({
    autoAssignCategories: false,
    loading: false,
    publishState: 'N/A',
    sections: [],
    settings: {
      apiAutosync: false,
      apiAutosyncDelete: false,
      apiAutosyncUpdate: false,
      automaticAssignment: false,
    },
    userCanPublish: false,
  });

  // Destructure values out of state for easier access.
  const {
    autoAssignCategories,
    loading,
    publishState,
    sections,
    settings: {
      apiAutosync,
      apiAutosyncDelete,
      apiAutosyncUpdate,
      automaticAssignment,
    },
    userCanPublish,
  } = state;

  // Get a reference to the dispatch function for notices for use later.
  const dispatchNotice = useDispatch('core/notices');

  // Get information about the current post.
  const {
    notices,
    postId,
    postIsDirty,
    postStatus,
  } = useSelect((select) => {
    const editor = select('core/editor');
    return {
      notices: editor.getEditedPostAttribute('apple_news_notices'),
      postId: editor.getCurrentPostId(),
      postIsDirty: editor.isEditedPostDirty(),
      postStatus: editor.getEditedPostAttribute('status'),
    };
  });

  // Get read-only values from postmeta.
  const [{
    apple_news_api_created_at: dateCreated,
    apple_news_api_id: apiId,
    apple_news_api_modified_at: dateModified,
    apple_news_api_revision: revision,
    apple_news_api_share_url: shareUrl,
  }] = usePostMeta();

  // Getters and setters for individual postmeta values.
  const [coverImageId, setCoverImageId] = usePostMetaValue('apple_news_coverimage');
  const [coverImageCaption, setCoverImageCaption] = usePostMetaValue('apple_news_coverimage_caption');
  const [isHidden, setIsHidden] = usePostMetaValue('apple_news_is_hidden');
  const [isPaid, setIsPaid] = usePostMetaValue('apple_news_is_paid');
  const [isPreview, setIsPreview] = usePostMetaValue('apple_news_is_preview');
  const [isSponsored, setIsSponsored] = usePostMetaValue('apple_news_is_sponsored');
  const [maturityRating, setMaturityRating] = usePostMetaValue('apple_news_maturity_rating');
  const [metadataRaw, setMetadataRaw] = usePostMetaValue('apple_news_metadata');
  const [pullquoteText, setPullquoteText] = usePostMetaValue('apple_news_pullquote');
  const [pullquotePosition, setPullquotePosition] = usePostMetaValue('apple_news_pullquote_position');
  const [selectedSections, setSelectedSectionsRaw] = usePostMetaValue('apple_news_sections');
  const [slug, setSlug] = usePostMetaValue('apple_news_slug');
  const [suppressVideoURL, setSuppressVideoURL] = usePostMetaValue('apple_news_suppress_video_url');
  const [useImageComponent, setUseImageComponent] = usePostMetaValue('apple_news_use_image_component');

  // Decode selected sections.
  const metadata = safeJsonParseArray(metadataRaw);

  /**
   * A helper function for setting metadata.
   * @param {object} next - The metadata value to set.
   */
  const setMetadata = (next) => setMetadataRaw(JSON.stringify(next));

  /**
   * A helper function for setting selected sections.
   * @param {Array} next - The array of selected sections to set.
   */
  const setSelectedSections = (next) => setSelectedSectionsRaw(next);

  /**
   * A helper function for displaying a notification to the user.
   * @param {string} message - The notification message displayed to the user.
   * @param {string} type - Optional. The type of message to display. Defaults to success.
   */
  const displayNotification = useCallback((message, type = 'success') => (type === 'success'
    ? dispatchNotice.createInfoNotice(DOMPurify.sanitize(message), { type: 'snackbar' })
    : dispatchNotice.createErrorNotice(message, { __unstableHTML: true })
  ), [dispatchNotice]);

  /**
   * Sends a request to the REST API to modify the post.
   * @param {string} operation - One of delete, publish, update.
   */
  const modifyPost = async (operation) => {
    setState({
      ...state,
      loading: true,
    });

    try {
      const {
        notifications = [],
        publishState: nextPublishState = '',
      } = await apiFetch({
        data: {
          id: postId,
        },
        method: 'POST',
        path: `/apple-news/v1/${operation}`,
      });
      notifications.forEach((notification) => displayNotification(
        notification.message,
        notification.type,
      ));
      setState({
        ...state,
        loading: false,
        publishState: nextPublishState,
      });
    } catch (error) {
      displayNotification(error.message, 'error');
      setState({
        ...state,
        loading: false,
      });
    }
  };

  /**
   * A helper function to update which sections are selected.
   * @param {string} id - The id of the section to toggle.
   */
  const toggleSelectedSection = (id) => setSelectedSections(
    selectedSections.includes(id)
      ? selectedSections.filter((section) => section !== id)
      : [...selectedSections, id],
  );

  // On initial load, fetch info from the API into state.
  useEffect(() => {
    (async () => {
      const fetches = [
        await apiFetch({ path: `/apple-news/v1/get-published-state/${postId}` }),
        await apiFetch({ path: '/apple-news/v1/sections' }),
        await apiFetch({ path: '/apple-news/v1/get-settings' }),
        await apiFetch({ path: `/apple-news/v1/user-can-publish/${postId}` }),
      ];

      // Wait for everything to load, update state, and handle errors.
      try {
        const data = await Promise.all(fetches);
        setState({
          ...state,
          autoAssignCategories: (selectedSections === null || selectedSections.length === 0)
            && data[2].automaticAssignment === true,
          ...data[0],
          sections: data[1],
          settings: data[2],
          ...data[3],
        });
      } catch (error) {
        displayNotification(error.message, 'error');
      }
    })();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  // Display notices whenever they change.
  useEffect(() => {
    /* Adding a conditional here to prevent a sporadic error.
    Leaving a console log in place in case we need to debug this further.
    See: https://github.com/alleyinteractive/apple-news/issues/1030 */
    if (Array.isArray(notices) && notices.length) {
      notices.forEach((notice) => displayNotification(notice.message, notice.type));
    } else {
      console.log('Notices dispatched, but none to display.'); // eslint-disable-line no-console
    }
  }, [displayNotification, notices]);

  return (
    <>
      <PluginSidebarMoreMenuItem target="publish-to-apple-news">
        {__('Apple News Options', 'apple-news')}
      </PluginSidebarMoreMenuItem>
      <PluginSidebar
        name="publish-to-apple-news"
        title={__('Publish to Apple News Options', 'apple-news')}
      >
        <Sections
          autoAssignCategories={autoAssignCategories}
          automaticAssignment={automaticAssignment}
          onChangeAutoAssignCategories={(next) => {
            setState({
              ...state,
              autoAssignCategories: next,
            });
            setSelectedSections([]);
          }}
          onChangeSelectedSections={toggleSelectedSection}
          sections={sections}
          selectedSections={selectedSections}
        />
        <Metadata
          isHidden={isHidden}
          isPaid={isPaid}
          isPreview={isPreview}
          isSponsored={isSponsored}
          metadata={metadata}
          onChangeIsHidden={setIsHidden}
          onChangeIsPaid={setIsPaid}
          onChangeIsPreview={setIsPreview}
          onChangeIsSponsored={setIsSponsored}
          onChangeMetadata={setMetadata}
          onChangeSuppressVideoURL={setSuppressVideoURL}
          onChangeUseImageComponent={setUseImageComponent}
          suppressVideoURL={suppressVideoURL}
          useImageComponent={useImageComponent}
        />
        <MaturityRating
          maturityRating={maturityRating}
          onChangeMaturityRating={setMaturityRating}
        />
        <Slug
          onChangeSlug={setSlug}
          slug={slug}
        />
        <PullQuote
          onChangePullquotePosition={setPullquotePosition}
          onChangePullquoteText={setPullquoteText}
          pullquotePosition={pullquotePosition}
          pullquoteText={pullquoteText}
        />
        <CoverImage
          coverImageCaption={coverImageCaption}
          coverImageId={coverImageId}
          onChangeCoverImageCaption={setCoverImageCaption}
          onChangeCoverImageId={setCoverImageId}
        />
        {publishState !== 'N/A' ? (
          <PublishInfo
            apiId={apiId}
            dateCreated={dateCreated}
            dateModified={dateModified}
            publishState={publishState}
            revision={revision}
            shareUrl={shareUrl}
          />
        ) : null}
        <PublishControls
          apiAutosync={apiAutosync}
          apiAutosyncDelete={apiAutosyncDelete}
          apiAutosyncUpdate={apiAutosyncUpdate}
          deletePost={() => modifyPost('delete')}
          loading={loading}
          postIsDirty={postIsDirty}
          postStatus={postStatus}
          publishPost={() => modifyPost('publish')}
          publishState={publishState}
          updatePost={() => modifyPost('update')}
          userCanPublish={userCanPublish}
        />
      </PluginSidebar>
    </>
  );
}

export default Sidebar;
