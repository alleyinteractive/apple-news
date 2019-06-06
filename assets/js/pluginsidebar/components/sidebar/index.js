/* global React, wp */

import PropTypes from 'prop-types';
import ImagePicker from '../imagePicker';

const {
  apiFetch,
  compose: {
    compose,
  },
  components: {
    PanelBody,
    CheckboxControl,
    SelectControl,
    TextareaControl,
  },
  data: {
    withDispatch,
    withSelect,
  },
  editPost: {
    PluginSidebar,
  },
  i18n: {
    __,
  },
} = wp;

/**
 * A component to render a PluginSidebar for the WP Starter Plugin site.
 */
class Sidebar extends React.PureComponent {
  // Define PropTypes for this component.
  static propTypes = {
    meta: PropTypes.shape({
      isPreview: PropTypes.bool,
      isHidden: PropTypes.bool,
      isSponsored: PropTypes.bool,
      maturityRating: PropTypes.string,
      pullquoteText: PropTypes.string,
      pullquotePosition: PropTypes.string,
      selectedSections: PropTypes.string,
      coverArt: PropTypes.shape({
        orientation: PropTypes.string,
        // Other keys are determined in part by orientation
        // see `coverArtSizes` variable below
      }),
      apiId: PropTypes.string,
      dateCreated: PropTypes.string,
      dateModified: PropTypes.string,
      shareUrl: PropTypes.string,
      revision: PropTypes.string,
    }).isRequired,
    onUpdate: PropTypes.func.isRequired,
    post: PropTypes.shape({}).isRequired,
  };

  constructor(props) {
    super(props);

    this.state = {
      notifications: [],
      sections: [],
      settings: {
        enableCoverArt: false,
        adminUrl: '',
      },
      publishState: '',
    };

    this.updateSelectedSections = this.updateSelectedSections.bind(this);
  }

  componentDidMount() {
    this.fetchSections();
    this.fetchSettings();
    this.fetchPublishState();
    this.fetchNotifications();
  }

  /**
   * Fetch Apple News notifications for the current user.
   */
  fetchNotifications() {
    const path = '/apple-news/v1/get-notifications';

    apiFetch({ path })
      .then((notifications) => (this.setState({ notifications })))
      .catch((error) => console.error(error)); /* eslint-disable-line no-console */
  }

  /**
   * Fetch Apple News Settings
   */
  fetchSettings() {
    const path = '/apple-news/v1/get-settings';

    apiFetch({ path })
      .then((settings) => (this.setState({ settings })))
      .catch((error) => console.error(error)); /* eslint-disable-line no-console */
  }

  /**
   * Fetch Apple News Sections
   */
  fetchSections() {
    const path = '/apple-news/v1/sections';

    apiFetch({ path })
      .then((sections) => (this.setState({ sections })))
      .catch((error) => console.error(error)); /* eslint-disable-line no-console */
  }

  fetchPublishState() {
    const {
      post,
    } = this.props;
    const path = `/apple-news/v1/get-published-state/${post.id}`;

    apiFetch({ path })
      .then(({ publishState }) => (this.setState({ publishState })))
      .catch((error) => console.error(error)); /* eslint-disable-line no-console */
  }

  updateSelectCoverArtImage(metaKey, value) {
    const {
      onUpdate,
      meta: {
        coverArt,
      },
    } = this.props;

    let parsedCoverArt = JSON.parse(coverArt);

    if (! value) {
      delete parsedCoverArt[metaKey];
    } else {
      parsedCoverArt = {
        [metaKey]: value,
        ...parsedCoverArt,
      };
    }

    onUpdate(
      'apple_news_coverart',
      JSON.stringify(parsedCoverArt)
    );
  }

  updateSelectedSections(checked, name) {
    const {
      onUpdate,
      meta: {
        selectedSections,
      },
    } = this.props;
    // Need to default to [], else JSON parse fails
    const selectedSectionsArray = JSON.parse(selectedSections || '[]');

    onUpdate(
      'apple_news_sections',
      JSON.stringify(
        checked
          ? [...selectedSectionsArray, name]
          : selectedSectionsArray.filter((section) => section !== name)
      )
    );
  }

  /**
   * Renders the PluginSidebar.
   * @returns {object} JSX component markup.
   */
  render() {
    const {
      meta: {
        isPreview = false,
        isHidden = false,
        isSponsored = false,
        maturityRating = '',
        pullquoteText = '',
        pullquotePosition = '',
        selectedSections = '',
        coverArt = '',
        apiId = '',
        dateCreated = '',
        dateModified = '',
        shareUrl = '',
        revision = '',
      },
      onUpdate,
    } = this.props;

    const {
      notifications,
      sections,
      settings: {
        enableCoverArt,
        adminUrl,
      },
      publishState,
    } = this.state;
    const selectedSectionsArray = JSON.parse(selectedSections || '[]');
    const parsedCoverArt = JSON.parse(coverArt || '{}');
    const coverArtOrientation = parsedCoverArt.orientation || 'landscape';
    const coverArtSizes = [
      {
        title: __('iPad Pro (12.9 in): 1832 x 1374 px', 'apple-news'),
        key: `apple_news_ca_${coverArtOrientation}_12_9`,
      },
      {
        title: __('iPad (7.9/9.7 in): 1376 x 1032 px', 'apple-news'),
        key: `apple_news_ca_${coverArtOrientation}_9_7`,
      },
      {
        title: __('iPhone (5.5 in): 1044 x 783 px', 'apple-news'),
        key: `apple_news_ca_${coverArtOrientation}_5_5`,
      },
      {
        title: __('iPhone (4.7 in): 632 x 474 px', 'apple-news'),
        key: `apple_news_ca_${coverArtOrientation}_4_7`,
      },
      {
        title: __('iPhone (4 in): 536 x 402 px', 'apple-news'),
        key: `apple_news_ca_${coverArtOrientation}_4_0`,
      },
    ];
    return (
      <PluginSidebar
        name="publish-to-apple-news"
        title={__('Publish to Apple News Options', 'apple-news')}
      >
        <div className="components-panel__body is-opened">
          {notifications.map((notification) => (
            <div key={notification.message}>
              {`${notification.type}: ${notification.message}`}
            </div>
          ))}
          <h3>Sections</h3>
          {
            sections.map(({ id, name }) => (
              <CheckboxControl
                key={id}
                label={name}
                checked={- 1 !== selectedSectionsArray.indexOf(id)}
                onChange={
                  (checked) => this.updateSelectedSections(checked, id)
                }
              />
            ))
          }
          <p>
            <em>
              {
                // eslint-disable-next-line max-len
                __('Select the sections in which to publish this article. If none are selected, it will be published to the default section.', 'apple-news')
              }
            </em>
          </p>
          <h3>{__('Preview Article', 'apple-news')}</h3>
          <CheckboxControl
            // eslint-disable-next-line max-len
            label={__('Check this to publish the article as a draft.', 'apple-news')}
            onChange={(value) => onUpdate(
              'apple_news_is_preview',
              value
            )}
            checked={isPreview}
          />
          <h3>Hidden Article</h3>
          <CheckboxControl
            // eslint-disable-next-line max-len
            label={__('Hidden articles are visible to users who have a link to the article, but do not appear in feeds.', 'apple-news')}
            onChange={(value) => onUpdate(
              'apple_news_is_hidden',
              value
            )}
            checked={isHidden}
          />
          <h3>Sponsored Article</h3>
          <CheckboxControl
            // eslint-disable-next-line max-len
            label={__('Check this to indicate this article is sponsored content.', 'apple-news')}
            onChange={(value) => onUpdate(
              'apple_news_is_sponsored',
              value
            )}
            checked={isSponsored}
          />
        </div>
        <PanelBody
          initialOpen={false}
          title={__('Maturity Rating', 'apple-news')}
        >
          <SelectControl
            label={__('Select Maturity Rating', 'apple-news')}
            value={maturityRating}
            options={[
              { label: '', value: '' },
              { label: __('Kids', 'apple-news'), value: 'KIDS' },
              { label: __('Mature', 'apple-news'), value: 'MATURE' },
              { label: __('General', 'apple-news'), value: 'GENERAL' },
            ]}
            onChange={(value) => onUpdate(
              'apple_news_maturity_rating',
              value
            )}
          />
          <p>
            <em>
              Select the optional maturity rating for this post.
            </em>
          </p>
        </PanelBody>
        <PanelBody
          initialOpen={false}
          title={__('Pull Quote', 'apple_news')}
        >
          <TextareaControl
            label={__('Description', 'apple_news')}
            value={pullquoteText}
            onChange={(value) => onUpdate(
              'apple_news_pullquote',
              value
            )}
            // eslint-disable-next-line max-len
            placeholder="A pull quote is a key phrase, quotation, or excerpt that has been pulled from an article and used as a graphic element, serving to entice readers into the article or to highlight a key topic."
          />
          <p>
            <em>
              This is optional and can be left blank.
            </em>
          </p>
          <SelectControl
            label={__('Pull Quote Position', 'apple-news')}
            value={pullquotePosition || 'middle'}
            options={[
              { label: __('top', 'apple-news'), value: 'top' },
              { label: __('middle', 'apple-news'), value: 'middle' },
              { label: __('bottom', 'apple-news'), value: 'bottom' },
            ]}
            onChange={(value) => onUpdate(
              'apple_news_pullquote_position',
              value
            )}
          />
          <p>
            <em>
              {
                // eslint-disable-next-line max-len
                __('The position in the article where the pull quote will appear.', 'apple-news')
              }
            </em>
          </p>
        </PanelBody>
        <PanelBody
          initialOpen={false}
          title={__('Cover Art', 'apple-news')}
        >
          {
            enableCoverArt ? (
              <div>
                <p>
                  <em>
                    <a href="https://developer.apple.com/library/content/documentation/General/Conceptual/Apple_News_Format_Ref/CoverArt.html">
                      {__('Cover Art', 'apple-news')}
                    </a>
                    {
                      // eslint-disable-next-line max-len
                      __(' will represent your article if editorially chosen for Featured Stories. Cover Art must include your channel logo with text at 24 pt minimum that is related to the headline. The image provided must match the dimensions listed. Limit submissions to 1-3 articles per day.', 'apple-news')
                    }
                  </em>
                </p>
                <SelectControl
                  label={__('Orientation', 'apple-news')}
                  value={coverArtOrientation}
                  options={[
                    /* eslint-disable max-len */
                    { label: __('Landscape (4:3)', 'apple-news'), value: 'landscape' },
                    { label: __('Portrait (3:4)', 'apple-news'), value: 'portrait' },
                    { label: __('Square (1:1)', 'apple-news'), value: 'square' },
                    /* eslint-enable */
                  ]}
                  onChange={(value) => {
                    const mediaKeys = Object
                      .keys(parsedCoverArt)
                      .filter((key) => 'orientation' !== key);
                    const updatedOrientation = {
                      orientation: value,
                    };

                    const updatedCoverArt = mediaKeys.reduce((acc, curr) => {
                      const newKey = curr.replace(coverArtOrientation, value);
                      return {
                        [newKey]: parsedCoverArt[curr],
                        ...acc,
                      };
                    }, updatedOrientation);

                    onUpdate(
                      'apple_news_coverart',
                      JSON.stringify(updatedCoverArt)
                    );
                  }}
                />
                <p>
                  <em>
                    {
                      // eslint-disable-next-line max-len
                      __('Note: You must provide the largest size (iPad Pro 12.9 in) in order for your submission to be considered.', 'apple-news')
                    }
                  </em>

                </p>
                <div>
                  {
                    coverArtSizes.map((item) => (
                      <div>
                        <h4>{item.title}</h4>
                        <ImagePicker
                          metaKey={item.key}
                          onUpdate={
                            (metaKey, value) => this.updateSelectCoverArtImage(
                              metaKey,
                              value
                            )
                          }
                          value={parsedCoverArt[item.key]}
                        />
                      </div>
                    ))
                  }
                </div>
              </div>
            ) : (
              <p>
                <em>
                  {__('Cover Art must be enabled on the ', 'apple-news')}
                  <a href={adminUrl}>
                    {__('settings page', 'apple-news')}
                  </a>
                </em>
              </p>
            )
          }
        </PanelBody>
        <PanelBody
          initialOpen={false}
          title={__('Apple News Publish Information', 'apple-news')}
        >
          <h4>{__('API Id', 'apple-news')}</h4>
          <p>{apiId}</p>
          <h4>{__('Created On', 'apple-news')}</h4>
          <p>{dateCreated}</p>
          <h4>{__('Last Updated On', 'apple-news')}</h4>
          <p>{dateModified}</p>
          <h4>{__('Share URL', 'apple-news')}</h4>
          <p>{shareUrl}</p>
          <h4>{__('Revision', 'apple-news')}</h4>
          <p>{revision}</p>
          <h4>{__('Publish State', 'apple-news')}</h4>
          <p>{publishState}</p>
        </PanelBody>
      </PluginSidebar>
    );
  }
}

export default compose([
  withSelect((select) => {
    const editor = select('core/editor');
    const {
      apple_news_is_preview: isPreview,
      apple_news_is_hidden: isHidden,
      apple_news_is_sponsored: isSponsored,
      apple_news_maturity_rating: maturityRating,
      apple_news_pullquote: pullquoteText,
      apple_news_pullquote_position: pullquotePosition,
      apple_news_sections: selectedSections,
      apple_news_coverart: coverArt,
      apple_news_api_id: apiId,
      apple_news_api_created_at: dateCreated,
      apple_news_api_modified_at: dateModified,
      apple_news_api_share_url: shareUrl,
      apple_news_api_revision: revision,
    } = editor.getEditedPostAttribute('meta');

    const postId = editor.getCurrentPostId();

    return {
      meta: {
        isPreview,
        isHidden,
        isSponsored,
        maturityRating,
        pullquoteText,
        pullquotePosition,
        selectedSections,
        coverArt,
        apiId,
        dateCreated,
        dateModified,
        shareUrl,
        revision,
        postId,
      },
      post: editor.getCurrentPost(),
    };
  }),
  withDispatch((dispatch) => ({
    onUpdate: (metaKey, metaValue) => {
      dispatch('core/editor').editPost({
        meta: {
          [metaKey]: metaValue,
        },
      });
    },
  })),
])(Sidebar);
