import safeJsonParse from './safeJsonParse';

/**
 * Given a value, run JSON.parse on it, but if parsing fails, or if
 * what results from the parse is not an array, return an empty
 * array rather than a syntax error or a value of another type.
 * @param {*} value - The value to attempt to parse.
 * @returns {array} - The parsed value, or an empty array on failure.
 */
export default function safeJsonParseArray(value) {
  const parsedValue = safeJsonParse(value);

  // Make absolutely sure that the parsed value is an array.
  if (! Array.isArray(parsedValue)) {
    return [];
  }

  return parsedValue;
}
