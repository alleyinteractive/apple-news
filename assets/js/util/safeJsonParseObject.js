import safeJsonParse from './safeJsonParse';

/**
 * Given a value, run JSON.parse on it, but if parsing fails, or if
 * what results from the parse is not a standard object, return an empty
 * object rather than a syntax error or a value of another type.
 * @param {*} value - The value to attempt to parse.
 * @returns {object} - The parsed value, or an empty object on failure.
 */
export default function safeJsonParseObject(value) {
  const parsedValue = safeJsonParse(value);

  // Make absolutely sure that the object is a standard object.
  if (null === parsedValue
    || 'object' !== typeof parsedValue
    || Array.isArray(parsedValue)
    || 0 !== JSON.stringify(parsedValue).indexOf('{')
  ) {
    return {};
  }

  return parsedValue;
}
