/* global React, wp */

import dompurify from 'dompurify';

/**
 * A component to render Apple News notifications.
 */
export default class Notifications extends React.PureComponent {
  // Set default state.
  state = {
    notifications: [],
  };

  /**
   * Actions to take after the component mounted.
   */
  componentDidMount() {
    const {
      data: {
        subscribe,
      },
    } = wp;

    // Kick off the initial fetch of notifications.
    this.fetchNotifications();

    // When the post is published or updated, we refresh notifications.
    subscribe(() => {
      const {
        data: {
          select,
        },
      } = wp;

      // If the update is anything other than a successful save, bail out.
      if (! select('core/editor').didPostSaveRequestSucceed()) {
        return;
      }

      // Re-fetch notifications.
      this.fetchNotifications();
    });
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
   *
   * Once notifications have been fetched, triggers an action to clear
   * notifications that should time out after a 1s delay. This covers
   * scenarios like a publish success or publish error message, where
   * they should display until the next update. Because the mechanism
   * for subscribing to updates in Gutenberg is a blunt instrument,
   * and several update actions are triggered in rapid succession, there
   * is not a reliable method to say "is the action that just completed
   * the post save action that resulted in Apple News updating" so we
   * need to pad it out a bit to avoid race conditions and/or the
   * message disappearing immediately after being shown before the user
   * can read it.
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
              () => setTimeout(this.clearNotifications, 1000)
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
