import { useEffect, useState } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const useSiteOptions = () => {
  const [loading, setLoading] = useState(true);
  const [notices, setNotices] = useState([]);
  const [saving, setSaving] = useState(false);
  const [settings, setSettings] = useState({});

  // Setup for Gutenberg's notices system.
  const {
    createErrorNotice,
    createSuccessNotice,
    removeNotice,
  } = useDispatch('core/notices');
  const noticeOptions = {
    type: 'snackbar',
    isDismissable: true,
  };

  /**
   * Helper for creating an error notice and adding it to the stack.
   * @param {string} message - The message to display to the user.
   */
  const error = async (message) => {
    const { notice: { id } = {} } = await createErrorNotice(message, noticeOptions);
    setNotices([...notices, id]);
  };

  /**
   * Helper for creating a success notice and adding it to the stack.
   * @param {string} message - The message to display to the user.
   */
  const success = async (message) => {
    const { notice: { id } = {} } = await createSuccessNotice(message, noticeOptions);
    setNotices([...notices, id]);
  };

  // Load settings on initial mount.
  useEffect(() => {
    (async () => {
      try {
        const response = await apiFetch({
          path: '/wp/v2/settings',
        });
        setSettings(response || {});
      } catch ({ message }) {
        await error(message);
      } finally {
        setLoading(false);
      }
    })();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  /**
   * Set settings.
   *
   * @param {object} newSettings settings object.
   */
  const saveSettings = async () => {
    setSaving(true);
    notices.forEach((id) => removeNotice(id));
    setNotices([]);

    // Enforce some defaults prior to save.
    // Request will 500 when site_logo === null.
    const finalSettings = { ...settings };
    finalSettings.site_logo = finalSettings.site_logo ?? 0;

    try {
      const response = await apiFetch({
        path: '/wp/v2/settings',
        method: 'POST',
        data: finalSettings,
      });
      setSettings(response || {});
      await success(__('Settings Saved', 'apple-news'));
    } catch ({ message }) {
      await error(message);
    } finally {
      setSaving(false);
    }
  };

  return [
    {
      loading,
      setSettings,
      saving,
      settings,
    },
    saveSettings,
  ];
};

export default useSiteOptions;
