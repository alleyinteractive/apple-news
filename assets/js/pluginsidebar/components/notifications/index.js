/* global React, wp */

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
      apiFetch,
    } = wp;
    const path = '/apple-news/v1/get-notifications';
    apiFetch({ path })
      .then((notifications) => {
        if (Array.isArray(notifications) && 0 < notifications.length) {
          this.setState({ notifications });
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
      notifications,
    } = this.state;

    return (
      <ul>
        {notifications.map((notification) => (
          <li key={notification.message}>
            {`${notification.type}: ${notification.message}`}
          </li>
        ))}
      </ul>
    );
  }
}
