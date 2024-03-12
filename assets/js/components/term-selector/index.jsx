/* eslint-disable react/jsx-props-no-spreading */
import { useDebounce } from '@alleyinteractive/block-editor-tools';
import apiFetch from '@wordpress/api-fetch';
import { SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import React, { useEffect, useState } from 'react';

// Hooks.
import useTaxonomies from '../../services/hooks/use-taxonomies';
import useTermCache from '../../services/hooks/use-term-cache';

export default function TermSelector({
  onChange,
  taxonomy,
  termId,
  ...rest
}) {
  const taxonomies = useTaxonomies();
  const termCache = useTermCache();
  const [searchResults, setSearchResults] = useState([]);
  const [searchTerm, setSearchTerm] = useState(null);
  const debouncedSearchTerm = useDebounce(searchTerm, 500);

  // If the debounced search term changes, search for results from the API.
  useEffect(() => {
    const fetchTermData = async () => {
      if (debouncedSearchTerm) {
        const newSearchResults = await apiFetch({
          path: `/${taxonomies[taxonomy].rest_namespace}/${taxonomies[taxonomy].rest_base}?search=${debouncedSearchTerm}`,
        });
        newSearchResults.forEach((result) => termCache.set(result));
        setSearchResults(newSearchResults);
      }
    };

    fetchTermData();
  }, [debouncedSearchTerm]); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <div>
      <TextControl
        onChange={setSearchTerm}
        value={searchTerm !== null ? searchTerm : termCache.get(taxonomy, termId)?.name ?? ''}
        {...rest}
      />
      {searchResults.length ? (
        <SelectControl
          label={__('Choose a term', 'apple-news')}
          onChange={(next) => {
            const nextTermId = parseInt(next, 10);
            setSearchResults([]);
            setSearchTerm(null);
            onChange(nextTermId);
          }}
          options={[
            { label: '', value: '' },
            ...searchResults.map(({ name: label, id: value }) => ({ label, value })),
          ]}
        />
      ) : null}
    </div>
  );
}

TermSelector.propTypes = {
  onChange: PropTypes.func.isRequired,
  taxonomy: PropTypes.string.isRequired,
  termId: PropTypes.number.isRequired,
};
