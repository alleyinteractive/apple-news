/* global describe, expect, it */

import safeJsonParseObject from './safeJsonParseObject';

describe('safeJsonParseObject', () => {
  it('Should properly return a parsed object.', () => {
    expect(safeJsonParseObject('{}')).toEqual({});
    expect(safeJsonParseObject('{"a": "b"}')).toEqual({ a: 'b' });
  });

  it('Should return an empty object for any non-object types.', () => {
    expect(safeJsonParseObject('true')).toEqual({});
    expect(safeJsonParseObject('"foo"')).toEqual({});
    expect(safeJsonParseObject('[1, 5, "false"]')).toEqual({});
    expect(safeJsonParseObject('null')).toEqual({});
    expect(safeJsonParseObject('["a", "b", "c"]')).toEqual({});
    expect(safeJsonParseObject('')).toEqual({});
    expect(safeJsonParseObject(undefined)).toEqual({});
  });
});
