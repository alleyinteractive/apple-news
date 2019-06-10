/* global React, wp */

import dompurify from 'dompurify';

/**
 * A component to render Apple News notifications.
 */
export default class Notifications extends React.PureComponent {
  // Set default state.
  state = {
    modified: 0,
    notifications: [],
    unsubscribe: undefined,
  };

  /**
   * Actions to take after the component mounted.
   */
  componentDidMount() {
    const {
      data: {
        select,
        subscribe,
      },
    } = wp;

    // Kick off the initial fetch of notifications.
    this.fetchNotifications();

    // When the post is published or updated, we refresh notifications.
    const unsubscribe = subscribe(() => {
      const {
        modified,
      } = this.state;

      // If the modified date has not changed, bail out.
      const newModified = select('core/editor')
        .getEditedPostAttribute('modified');
      if (modified === newModified) {
        return;
      }

      // Update the modified date in state and fetch notifications.
      this.setState(
        {
          modified: newModified,
        },
        this.fetchNotifications
      );
    });

    // Add the last modified date and unsubscribe to state.
    this.setState({
      modified: select('core/editor').getEditedPostAttribute('modified'),
      unsubscribe,
    });
  }

  /**
   * De-initializes functionality before the component is destroyed.
   */
  componentWillUnmount() {
    const {
      unsubscribe,
    } = this.state;

    if (unsubscribe) {
      unsubscribe();
    }
  }

  /**
   * Clears notifications that should be displayed once and automatically removed.
   */
  clearNotifications() {
    const {
      apiFetch,
    } = wp;
    const {
      notifications,
    } = this.state;

    // Ensure we have an array to loop over.
    if (! Array.isArray(notifications)) {
      return;
    }

    // Loop over the array of notifications and determine which ones we need to clear.
    const toClear = notifications
      .filter((notification) => true !== notification.dismissible);

    // Ensure there are items to be cleared.
    if (0 === toClear.length) {
      return;
    }

    // Send the request to the API to clear the notifications.
    apiFetch({
      data: {
        toClear,
      },
      method: 'POST',
      path: '/apple-news/v1/clear-notifications',
    })
      .catch((error) => console.error(error)); /* eslint-disable-line no-console */
  }

  /**
   * Fetches notifications for the current user via the REST API.
   */
  fetchNotifications() {
    const {
      apiFetch,
    } = wp;
    const path = '/apple-news/v1/get-notifications';
    apiFetch({ path })
      .then((notifications) => {
        if (Array.isArray(notifications)) {
          if (0 < notifications.length) {
            this.setState(
              {
                notifications,
              },
              this.clearNotifications
            );
          } else {
            this.setState({
              notifications,
            });
          }
        }
      })
      .catch((error) => console.error(error)); /* eslint-disable-line no-console */
  }

  /**
   * Renders this component.
   *
   * @returns object JSX for this component.
   */
  render() {
    const {
      Fragment,
    } = React;
    const {
      components: {
        Notice,
      },
    } = wp;
    const {
      notifications,
    } = this.state;

    return (
      <Fragment>
        {notifications.map((notification) => (
          <Notice
            isDismissible={true === notification.dismissible}
            key={notification.message}
            onRemove={() => console.log(notification.message)} // eslint-disable-line
            status={notification.type}
          >
            <p
              dangerouslySetInnerHTML={{ // eslint-disable-line react/no-danger
                __html: dompurify.sanitize(notification.message),
              }}
            />
          </Notice>
        ))}
      </Fragment>
    );
  }
}
