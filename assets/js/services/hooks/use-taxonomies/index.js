import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from 'react';

/**
 * A React hook for working with taxonomy configuration data from the WordPress
 * REST API. Caches responses for future use.
 * @returns {object} An object with taxonomy slugs as keys and responses as objects.
 */
export default function useTaxonomies() {
  const [taxonomies, setTaxonomies] = useState({});

  useEffect(() => {
    const fetchTaxonomies = async () => {
      const response = await apiFetch({ path: '/wp/v2/taxonomies' });
      setTaxonomies(response);
    };

    fetchTaxonomies();
  }, []);

  return taxonomies;
}
