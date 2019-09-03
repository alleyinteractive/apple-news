/* global React, wp */

import dompurify from 'dompurify';
import PropTypes from 'prop-types';

const {
  components: {
    Notice,
  },
  element: {
    Fragment,
  },
} = wp;

/**
 * A component to render Apple News notifications.
 */
export default class Notifications extends React.PureComponent {
  // Define PropTypes for this component.
  static propTypes = {
    dismissNotification: PropTypes.func.isRequired,
    notifications: PropTypes.arrayOf(PropTypes.shape({
      dismissed: PropTypes.bool,
      dismissible: PropTypes.bool,
      message: PropTypes.string,
      type: PropTypes.string,
    })).isRequired,
  };

  /**
   * Renders this component.
   *
   * @returns object JSX for this component.
   */
  render() {
    const {
      dismissNotification,
      notifications,
    } = this.props;

    const visibleNotifications = notifications
      .filter((notification) => true !== notification.dismissed);

    return (
      <Fragment>
        {visibleNotifications.map((notification) => (
          <Notice
            isDismissible={true === notification.dismissible}
            key={notification.message}
            onRemove={() => dismissNotification(notification)}
            status={notification.type}
          >
            <p
              // phpcs:ignore WordPressVIPMinimum.JS.DangerouslySetInnerHTML.Found
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
