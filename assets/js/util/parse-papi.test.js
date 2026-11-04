import parsePapi from './parse-papi';

describe('parsePapi', () => {
  it('reads the channel ID, key and secret from a .papi file', () => {
    const papi = 'channel_id: abc-123\nkey: key-456\nsecret: secret-789\n';

    expect(parsePapi(papi)).toEqual({
      channel: 'abc-123',
      key: 'key-456',
      secret: 'secret-789',
    });
  });

  it('returns empty strings for lines that are missing', () => {
    expect(parsePapi('channel_id: abc-123')).toEqual({
      channel: 'abc-123',
      key: '',
      secret: '',
    });
  });
});
