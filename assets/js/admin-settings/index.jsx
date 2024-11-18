/* global AppleNewsAutomationConfig */
import {
  Button,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';

// Components.
import Rule from './rule';

// Hooks.
import useSiteOptions from '../services/hooks/use-site-options';

// Util.
import deleteAtIndex from '../util/delete-at-index';
import updateValueAtIndex from '../util/update-value-at-index';

function AdminSettings() {
  const [{
    loading, setSettings, saving, settings,
  }, saveSettings] = useSiteOptions();
  const busy = loading || saving;
  const { apple_news_automation: ruleList } = settings;
  const { fields } = AppleNewsAutomationConfig;
  const sectionRows = [];
  const nonSectionRows = [];

  if (!ruleList) {
    return null;
  }

  /**
   * Helper function for pushing to in-memory settings inside useSiteOptions.
   * @param {array} updatedRules - The new array of rules.
   */
  const updateSettings = (updatedRules) => {
    setSettings({ ...settings, apple_news_automation: updatedRules });
  };

  /**
   * Adds a new empty rule to the end of the list.
   */
  const addRule = (field = '') => {
    updateSettings([
      ...(ruleList ?? []),
      {
        field,
        taxonomy: '',
        term_id: 0,
        value: '',
      },
    ]);
  };

  /**
   * Drag and drop logic/re-indexing for Rules.
   * @param {number} from - The origin index.
   * @param {number} to - The destination index.
   */
  const reorderRule = (from, to) => {
    if (from !== to) {
      const updatedRules = [...(ruleList ?? [])];
      [updatedRules[from], updatedRules[to]] = [updatedRules[to], updatedRules[from]];
      updateSettings(updatedRules);
    }
  };

  /**
   * Updates a configuration parameter for a rule given the rule index, a field
   * key, and a field value.
   * @param {number} index - The index of the rule being updated.
   * @param {string} key - The field key within the rule.
   * @param {string|number} value - A number for term_id, string otherwise.
   */
  const updateRule = (index, key, value) => {
    let updatedRules = updateValueAtIndex(ruleList, key, value, index);
    // Need to reset value state in case field changes the resulting value's type.
    if (key === 'field') {
      updatedRules = updateValueAtIndex(updatedRules, 'value', fields[value]?.type === 'boolean' ? 'false' : '', index);
    }
    updateSettings(updatedRules);
  };

  // Split the rows into sections and non-sections.
  ruleList.forEach((item, index) => {
    const row = (
      <Rule
        busy={busy}
        field={item.field}
        key={index} // eslint-disable-line react/no-array-index-key
        onDelete={() => updateSettings(deleteAtIndex(ruleList, index))}
        onDragEnd={(e) => {
          const targetRow = document
            .elementFromPoint(e.clientX, e.clientY)
            .closest('.apple-news-automation-row');
          if (targetRow) {
            reorderRule(
              e.currentTarget.dataset.index,
              targetRow.dataset.index,
            );
          }
        }}
        onUpdate={(key, value) => updateRule(index, key, value)}
        taxonomy={item.taxonomy}
        termId={item.term_id}
        value={item.value}
        index={index}
      />
    );
    if (item.field === 'links.sections') {
      sectionRows.push(row);
    } else {
      nonSectionRows.push(row);
    }
  });

  return (
    <div className="apple-news-options__wrapper">
      <h1>{__('Apple News Automation', 'apple-news')}</h1>
      <p>{__('Configure automation rules below to automatically apply certain settings based on the taxonomy terms applied to each post.', 'apple-news')}</p>
      <p>
        <a target="_blank" rel="noreferrer" href="https://github.com/alleyinteractive/apple-news/wiki/Automation">{__('For more information on how automation works, visit our wiki.', 'apple-news')}</a>
      </p>
      <table className="wp-list-table widefat fixed striped">
        <thead>
          <tr>
            <th id="apple-news-automation-column-taxonomy" scope="col">{__('Taxonomy', 'apple-news')}</th>
            <th id="apple-news-automation-column-term" scope="col">{__('Term', 'apple-news')}</th>
            <th id="apple-news-automation-column-field" scope="col">{__('Field', 'apple-news')}</th>
            <th id="apple-news-automation-column-value" scope="col">{__('Value', 'apple-news')}</th>
            <th id="apple-news-automation-column-delete" scope="col">{__('Delete?', 'apple-news')}</th>
          </tr>
        </thead>
        <tbody>
          {sectionRows}
        </tbody>
      </table>
      <div className="tablenav bottom" style={{ height: '50px' }}>
        <div className="alignleft actions">
          <Button
            disabled={busy}
            isSecondary
            onClick={() => addRule('links.sections')}
          >
            {__('Add Rule', 'apple-news')}
          </Button>
          {' '}
          <Button
            disabled={busy}
            isPrimary
            onClick={saveSettings}
          >
            {__('Save Settings', 'apple-news')}
          </Button>
        </div>
      </div>
      <hr />
      <h2 className="title">Additional Automation</h2>
      <table className="wp-list-table widefat fixed striped">
        <thead>
          <tr>
            <th id="apple-news-automation-column-taxonomy" scope="col">{__('Taxonomy', 'apple-news')}</th>
            <th id="apple-news-automation-column-term" scope="col">{__('Term', 'apple-news')}</th>
            <th id="apple-news-automation-column-field" scope="col">{__('Field', 'apple-news')}</th>
            <th id="apple-news-automation-column-value" scope="col">{__('Value', 'apple-news')}</th>
            <th id="apple-news-automation-column-delete" scope="col">{__('Delete?', 'apple-news')}</th>
          </tr>
        </thead>
        <tbody>
          {nonSectionRows}
        </tbody>
      </table>
      <div className="tablenav bottom">
        <div className="alignleft actions">
          <Button
            disabled={busy}
            isSecondary
            onClick={addRule}
            style={{ marginTop: '10px' }}
          >
            {__('Add Rule', 'apple-news')}
          </Button>
          {' '}
          <Button
            disabled={busy}
            isPrimary
            onClick={saveSettings}
          >
            {__('Save Settings', 'apple-news')}
          </Button>
        </div>
      </div>
    </div>
  );
}

export default AdminSettings;
