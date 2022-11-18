/* global wp, wpLocalizedData */
import {
  Button,
  SelectControl,
  TextControl,
  ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { useSelect } from '@wordpress/data';
import { ruleCard } from './styles';


const Rule = ({
  busy,
  field,
  onDelete,
  onUpdate,
  reorderRule,
  ruleIndex,
  setOriginIndex,
  setTargetIndex,
  taxonomy,
  term_id,
  value,
}) => {
  const {
    fields,
    sections,
    taxonomies,
    themes,
  } = wpLocalizedData;

  // const { loadingTerms, taxTerms } = useSelect((select) => ({
  //   loadingTerms: select('core/data').isResolving('core', 'getEntityRecords', ['taxonomy', 'category']),
  //   taxTerms: select('core').getEntityRecords('taxonomy', 'category') || [],
  // }));
  // if(!loadingTerms) {
  //   console.log(taxTerms)
  // }

  return (
    <div
      className="rule-wrapper"
      draggable
      style={ruleCard}
      onDragEnd={(e) => {
        const targetEl = document.elementFromPoint(e.clientX, e.clientY);
        // Only reorder if the target element is inside rule flex container.
        if (targetEl.closest('.rule-wrapper')) {
          reorderRule();
        }
      }}
      onDragStart={() => setOriginIndex(ruleIndex)}
      onDragOver={(e) => {
        e.preventDefault();
        setTargetIndex(ruleIndex)
      }}
    >
      <SelectControl
        disabled={busy}
        label={__('Taxonomy', 'apple-news')}
        onChange={(next) => onUpdate(ruleIndex, 'taxonomy', next)}
        options={[
          { value: '', label: 'Select Taxonomy' },
          ...Object.keys(taxonomies).map((tax) => ({ value: tax, label: tax }))
        ]}
        value={taxonomy}
      />
      <TextControl
        disabled={busy}
        label={__('Term ID', 'apple-news')}
        onChange={(next) => onUpdate(ruleIndex, 'term_id', next)}
        type="number"
        value={term_id}
      />
      <SelectControl
        disabled={busy}
        label={__('Field', 'apple-news')}
        onChange={(next) => onUpdate(ruleIndex, 'field', next)}
        options={[
          { value: '', label: 'Select Field' },
          ...Object.keys(fields).map((field) => ({ value: field, label: field }))
        ]}
        value={field}
      />
      {field === 'Section' ? (
        <SelectControl
          disabled={busy}
          label={__('Sections', 'apple-news')}
          onChange={(next) => onUpdate(ruleIndex, 'value', next)}
          options={[
            { value: '', label: 'Select Section' },
            ...sections.map((sect) => ({ value: sect.id, label: sect.name }))
          ]}
          value={value}
        />
      ):null}
      {fields[field] && fields[field].type === 'boolean' ? (
        <ToggleControl
          checked={value === 'true'}
          disabled={busy}
          label={__('True or False', 'apple-news')}
          onChange={(next) => onUpdate(ruleIndex, 'value', next.toString())}
        />
      ):null}
      {field === 'Slug' ? (
        <TextControl
          disabled={busy}
          label={__('Slug', 'apple-news')}
          onChange={(next) => onUpdate(ruleIndex, 'value', next)}
          value={value}
        />
      ):null}
      {field === 'Theme' ? (
        <SelectControl
          disabled={busy}
          label={__('Themes', 'apple-news')}
          onChange={(next) => onUpdate(ruleIndex, 'value', next)}
          options={[
            { value: '', label: 'Select Theme' },
            ...themes.map((name) => ({ value: name, label: name }))
          ]}
          value={value}
        />
      ):null}
      <Button
        disabled={busy}
        isDestructive
        onClick={()=> onDelete(ruleIndex)}
      >
        {__('Delete Rule', 'apple-news')}
      </Button>
    </div>
  );
};

export default Rule;
