import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from 'react';

// Hooks.
import useTaxonomies from '../use-taxonomies';

/**
 * A React hook for working with taxonomy terms. Allows fetching and setting
 * taxonomy terms for various taxonomies by ID. Caches results for future use.
 * @returns {object} An object with a get and set method.
 */
export default function useTermCache() {
  const [termCache, setTermCache] = useState({});
  const taxonomies = useTaxonomies();

  /**
   * Sets data for a term by taxonomy slug and term ID.
   * @param {object} termObject - The term object to set for the taxonomy and term ID.
   */
  const set = (termObject) => {
    if (termObject.taxonomy
      && termObject.id
      && (undefined === termCache[termObject.taxonomy]?.[termObject.id]
        || termCache[termObject.taxonomy][termObject.id].loading === true)
    ) {
      setTermCache({
        ...termCache,
        [termObject.taxonomy]: {
          ...(termCache[termObject.taxonomy] ?? {}),
          [termObject.id]: termObject,
        },
      });
    }
  };

  /**
   * Fetches a term from the REST API given the taxonomy slug and the term ID.
   * @param {string} taxonomy - The taxonomy slug.
   * @param {string} rawTermId - The term ID.
   */
  const fetchTerm = async (taxonomy, rawTermId) => {
    const termId = Number(rawTermId);
    if (Number.isNaN(termId) || termId <= 0) {
      return;
    }
    // If necessary, immediately add a placeholder to state while we are waiting for the load.
    if (termCache[taxonomy]?.[termId] === undefined) {
      set({ taxonomy, id: termId, loading: true });
    }

    // If taxonomies haven't loaded yet, bail out and fetch them later via useEffect.
    if (!taxonomies[taxonomy]) {
      return;
    }

    // Fetch the actual term object from the API.
    const termObject = await apiFetch({ path: `/${taxonomies[taxonomy].rest_namespace}/${taxonomies[taxonomy].rest_base}/${termId}` });
    if (termObject.name) {
      set(termObject);
    }
  };

  /**
   * Gets a term by taxonomy slug and term ID.
   * @param {string} taxonomy - The taxonomy slug to look up.
   * @param {number} termId - The term ID to look up.
   * @returns {object} The term object as it was returned by the REST API.
   */
  const get = (taxonomy, termId) => {
    if (termCache[taxonomy]?.[termId] === undefined) {
      fetchTerm(taxonomy, termId);
    }

    return termCache[taxonomy]?.[termId] ?? {};
  };

  // Handle cases where terms were requested before taxonomy config was loaded.
  useEffect(() => {
    if (Object.keys(taxonomies).length > 0) {
      Object.keys(termCache).forEach((taxonomy) => {
        Object.keys(termCache[taxonomy]).forEach((termId) => {
          if (termCache[taxonomy]?.[termId]?.loading === true) {
            fetchTerm(taxonomy, termId);
          }
        });
      });
    }
  }, [taxonomies]); // eslint-disable-line react-hooks/exhaustive-deps

  return { get, set };
}
