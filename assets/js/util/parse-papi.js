/**
 * Reads the channel ID, key and secret out of the contents of a .papi file.
 * @param {string} contents - The text of the .papi file.
 * @returns {{channel: string, key: string, secret: string}} - The credentials, with an
 *   empty string for any line that is missing.
 */
const parsePapi = (contents) => {
  const read = (name) => {
    const match = new RegExp(`${name}: ([^\\s]+)`).exec(contents);
    return match ? match[1] : '';
  };

  return {
    channel: read('channel_id'),
    key: read('key'),
    secret: read('secret'),
  };
};

export default parsePapi;
