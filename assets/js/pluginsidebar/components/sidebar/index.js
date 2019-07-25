/* global React, wp */

/* eslint-disable react/no-unused-state */

import PropTypes from 'prop-types';
import ImagePicker from '../imagePicker';
import Notifications from '../notifications';

const {
  apiFetch,
  compose: {
    compose,
  },
  components: {
    Button,
    CheckboxControl,
    PanelBody,
    SelectControl,
    TextareaControl,
  },
  data: {
    withDispatch,
    withSelect,
  },
  editPost: {
    PluginSidebar,
    PluginSidebarMoreMenuItem,
  },
  element: {
    Fragment,
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

  /**
   * Set initial state.
   * @type {object}
   */
  state = {
    autoAssignCategories: false,
    sections: [],
    selectedSectionsPrev: null,
    settings: {},
    publishState: '',
  };

  constructor(props) {
    super(props);

    this.deletePost = this.deletePost.bind(this);
    this.publishPost = this.publishPost.bind(this);
    this.updatePost = this.updatePost.bind(this);
    this.updateSelectedSections = this.updateSelectedSections.bind(this);
  }

  componentDidMount() {
    this.fetchSections();
    this.fetchSettings();
    this.fetchPublishState();
  }

  /**
   * Sends a request to the REST API to delete the post.
   */
  deletePost() {
    const {
      post: {
        id = 0,
      } = {},
    } = this.props;

    const path = '/apple-news/v1/delete';

    apiFetch({
      data: {
        id,
      },
      method: 'POST',
      path,
    })
      .then((data) => console.log(data))
      .catch((error) => console.log(error)); // eslint-disable-line no-console
  }

  /**
   * Fetch Apple News Settings
   */
  fetchSettings() {
    const path = '/apple-news/v1/get-settings';

    const {
      meta: {
        selectedSections,
      },
    } = this.props;

    apiFetch({ path })
      .then((settings) => this.setState({
        autoAssignCategories: 'null' === selectedSections
          && true === settings.automaticAssignment,
        settings,
      }))
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

  /**
   * Fetch published state.
   */
  fetchPublishState() {
    const {
      post,
    } = this.props;
    const path = `/apple-news/v1/get-published-state/${post.id}`;

    apiFetch({ path })
      .then(({ publishState }) => (this.setState({ publishState })))
      .catch((error) => console.error(error)); /* eslint-disable-line no-console */
  }

  /**
   * Sends a request to the REST API to publish the post.
   */
  publishPost() {
    const {
      post: {
        id = 0,
      } = {},
    } = this.props;

    const path = '/apple-news/v1/publish';

    apiFetch({
      data: {
        id,
      },
      method: 'POST',
      path,
    })
      .then((data) => console.log(data))
      .catch((error) => console.log(error)); // eslint-disable-line no-console
  }

  /**
   * Sends a request to the REST API to update the post.
   */
  updatePost() {
    const {
      post: {
        id = 0,
      } = {},
    } = this.props;

    const path = '/apple-news/v1/update';

    apiFetch({
      data: {
        id,
      },
      method: 'POST',
      path,
    })
      .then((data) => console.log(data))
      .catch((error) => console.log(error)); // eslint-disable-line no-console
  }

  /**
   * Select Cover Art Image
   *
   * @param   {string}  metaKey  metakey name
   * @param   {string}  value    meta key value
   */
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

  /**
   * Update which sections are selected.
   *
   * @param   {boolean} checked  is selected
   * @param   {string}  name     name of item selected
   *
   * @return void.
   */
  updateSelectedSections(checked, name) {
    const {
      onUpdate,
      meta: {
        selectedSections,
      },
    } = this.props;
    // Need to default to [], else JSON parse fails
    const selectedSectionsArray = JSON.parse(selectedSections) || [];

    const selectedArrayDefault = Array.isArray(selectedSectionsArray)
      ? JSON.stringify([...selectedSectionsArray, name]) : null;

    const arrayFilter = selectedSectionsArray.filter(
      (section) => section !== name
    );

    const selectedArrayFilter = 0 < arrayFilter.length
      ? JSON.stringify(arrayFilter) : null;

    onUpdate(
      'apple_news_sections',
      checked
        ? selectedArrayDefault
        : selectedArrayFilter
    );
  }

  /**
   * Renders the PluginSidebar.
   * @returns {object} JSX component markup.
   */
  render() {
    const target = 'publish-to-apple-news';
    const label = __('Apple News Options', 'apple-news');

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
      autoAssignCategories,
      sections,
      settings: {
        adminUrl,
        apiAutosync,
        apiAutosyncDelete,
        apiAutosyncUpdate,
        automaticAssignment,
        enableCoverArt,
      },
      selectedSectionsPrev,
      publishState,
    } = this.state;

    const selectedSectionsRaw = 'null' !== selectedSections
      ? JSON.parse(selectedSections)
      : '';

    const selectedSectionsArray = Array.isArray(selectedSectionsRaw)
      ? selectedSectionsRaw
      : [];

    const parsedCoverArt = '' !== coverArt && 'null' !== coverArt
      ? JSON.parse(coverArt) : {};

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
      <Fragment>
        <PluginSidebarMoreMenuItem target={target}>
          {label}
        </PluginSidebarMoreMenuItem>
        <PluginSidebar
          name={target}
          title={__('Publish to Apple News Options', 'apple-news')}
        >
          <div
            className="components-panel__body is-opened"
            id="apple-news-publish"
          >
            <Notifications />
            <h3>Sections</h3>
            {automaticAssignment && [
              <CheckboxControl
                label={__('Assign sections by category', 'apple-news')}
                checked={autoAssignCategories}
                onChange={
                  (checked) => {
                    this.setState({
                      autoAssignCategories: checked,
                    });
                    if (checked) {
                      this.setState({
                        selectedSectionsPrev: selectedSections || null,
                      });
                      onUpdate(
                        'apple_news_sections',
                        null
                      );
                    } else {
                      onUpdate(
                        'apple_news_sections',
                        selectedSectionsPrev
                      );
                      this.setState({
                        selectedSectionsPrev: null,
                      });
                    }
                  }
                }
              />,
              <hr />,
            ]}
            {(! autoAssignCategories || ! automaticAssignment) && [
              <h4>Manual Section Selection</h4>,
              Array.isArray(sections) && (
                <ul className="apple-news-sections">
                  {sections.map(({ id, name }) => (
                    <li key={id}>
                      <CheckboxControl
                        label={name}
                        checked={- 1 !== selectedSectionsArray.indexOf(id)}
                        onChange={
                          (checked) => this.updateSelectedSections(checked, id)
                        }
                      />
                    </li>
                  ))}
                </ul>
              ),
            ]}
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
                            onUpdate={(metaKey, value) => this
                              .updateSelectCoverArtImage(
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
            {'' !== publishState && 'N/A' !== publishState ? (
              <Fragment>
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
                {! apiAutosyncUpdate && (
                  <Button isPrimary onClick={this.updatePost}>
                    {__('Update', 'apple-news')}
                  </Button>
                )}
                {! apiAutosyncDelete && (
                  <Button isDestructive onClick={this.deletePost}>
                    {__('Delete', 'apple-news')}
                  </Button>
                )}
              </Fragment>
            ) : (
              <Fragment>
                {! apiAutosync && (
                  <Button isPrimary onClick={this.publishPost}>
                    {__('Publish', 'apple-news')}
                  </Button>
                )}
              </Fragment>
            )}
          </PanelBody>
        </PluginSidebar>
      </Fragment>
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
