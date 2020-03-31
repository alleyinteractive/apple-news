/* global describe, expect, it */

import safeJsonParseArray from './safeJsonParseArray';

describe('safeJsonParseArray', () => {
  it('Should properly return a parsed array.', () => {
    expect(safeJsonParseArray('[1, 5, "false"]')).toEqual([1, 5, 'false']);
    expect(safeJsonParseArray('["a", "b", "c"]')).toEqual(['a', 'b', 'c']);
  });

  it('Should return an empty array for any non-array types.', () => {
    expect(safeJsonParseArray('true')).toEqual([]);
    expect(safeJsonParseArray('"foo"')).toEqual([]);
    expect(safeJsonParseArray('null')).toEqual([]);
    expect(safeJsonParseArray('{}')).toEqual([]);
    expect(safeJsonParseArray('{"a": "b"}')).toEqual([]);
    expect(safeJsonParseArray('')).toEqual([]);
    expect(safeJsonParseArray(undefined)).toEqual([]);
  });
});
