import { addChannelRow, applyPapiToRow, removeChannelRow } from './rows';

document.addEventListener('click', (event) => {
  if (event.target.matches('.apple-news-channel-add')) {
    addChannelRow(document);
  } else if (event.target.matches('.apple-news-channel-remove')) {
    removeChannelRow(event.target);
  }
});

// Rows added after load need these handlers too, so they listen on the document.
document.addEventListener('change', (event) => {
  const row = event.target.closest('.apple-news-channel-row');
  if (!row) {
    return;
  }

  if (event.target.matches('.apple-news-channel-papi-input')) {
    applyPapiToRow(row, event.target.value);
  } else if (event.target.matches('.apple-news-channel-papi-file') && event.target.files?.[0]) {
    event.target.files[0].text().then((contents) => applyPapiToRow(row, contents));
  }
});
