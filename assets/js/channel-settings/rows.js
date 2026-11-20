import parsePapi from '../util/parse-papi';

/**
 * Adds an empty channel row from the row template.
 * @param {Document} doc - The settings page document.
 */
export const addChannelRow = (doc) => {
  const tbody = doc.querySelector('.apple-news-channels tbody');
  const template = doc.getElementById('apple-news-channel-row-template');

  // Saved rows are numbered from 0 by the server, so the next index never collides with them.
  const indexes = [...tbody.querySelectorAll('input[name^="apple_news_channels["]')]
    .map((input) => Number(input.name.match(/^apple_news_channels\[(\d+)\]/)?.[1] ?? -1));
  const nextIndex = String(Math.max(-1, ...indexes) + 1);

  tbody.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex));
};

/**
 * Removes the channel row a remove button belongs to.
 * @param {HTMLElement} button - The row's remove button.
 */
export const removeChannelRow = (button) => {
  button.closest('.apple-news-channel-row').remove();
};

/**
 * Fills a row's hidden credential fields from the contents of a .papi file.
 * @param {HTMLElement} row - The channel row.
 * @param {string} contents - The text of the .papi file.
 */
export const applyPapiToRow = (row, contents) => {
  const { channel, key, secret } = parsePapi(contents);
  const fields = {
    '.apple-news-channel-id': channel,
    '.apple-news-channel-key': key,
    '.apple-news-channel-secret': secret,
  };
  Object.entries(fields).forEach(([selector, value]) => {
    row.querySelector(selector).setAttribute('value', value);
  });
};
