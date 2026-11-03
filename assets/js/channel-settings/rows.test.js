/**
 * @jest-environment jsdom
 */
import { addChannelRow, applyPapiToRow, removeChannelRow } from './rows';

const rowHtml = (index) => `<tr class="apple-news-channel-row">
  <td><input name="apple_news_channels[${index}][slug]" /></td>
  <td><input type="hidden" class="apple-news-channel-id" name="apple_news_channels[${index}][channel]" />
  <input type="hidden" class="apple-news-channel-key" />
  <input type="hidden" class="apple-news-channel-secret" />
  <button type="button" class="apple-news-channel-remove"></button></td></tr>`;

beforeEach(() => {
  document.body.innerHTML = `<table class="apple-news-channels"><tbody>${rowHtml(0)}</tbody></table>
    <template id="apple-news-channel-row-template">${rowHtml('__INDEX__')}</template>`;
});

describe('channel rows', () => {
  it('adds a row whose fields use the next free index', () => {
    addChannelRow(document);

    const rows = document.querySelectorAll('.apple-news-channels tbody tr');
    expect(rows).toHaveLength(2);
    expect(rows[1].querySelector('input').name).toBe('apple_news_channels[1][slug]');
  });

  it('removes the row a remove button belongs to', () => {
    removeChannelRow(document.querySelector('.apple-news-channel-remove'));

    expect(document.querySelectorAll('.apple-news-channels tbody tr')).toHaveLength(0);
  });

  it('fills a row\'s hidden credentials from .papi contents', () => {
    const row = document.querySelector('.apple-news-channel-row');

    applyPapiToRow(row, 'channel_id: abc\nkey: def\nsecret: ghi');

    expect(row.querySelector('.apple-news-channel-id').value).toBe('abc');
    expect(row.querySelector('.apple-news-channel-key').value).toBe('def');
    expect(row.querySelector('.apple-news-channel-secret').value).toBe('ghi');
  });
});
