import {
  BaseControl,
  CheckboxControl,
  PanelBody,
  Spinner,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import React from 'react';

// Config.
import { SECTION_SHAPE } from '../../config/prop-types';

function Sections({
  autoAssignCategories,
  automaticAssignment,
  onChangeAutoAssignCategories,
  onChangeSelectedSections,
  sections,
  selectedSections,
}) {
  return (
    <PanelBody
      initialOpen={false}
      title={__('Sections', 'apple-news')}
    >
      {!Array.isArray(sections) || sections.length === 0 ? (
        <Spinner />
      ) : (
        <>
          {automaticAssignment ? (
            <CheckboxControl
              checked={autoAssignCategories}
              label={__('Assign sections by category', 'apple-news')}
              onChange={onChangeAutoAssignCategories}
            />
          ) : null}
          {automaticAssignment && !autoAssignCategories ? <hr /> : null}
          {(!automaticAssignment || !autoAssignCategories) ? (
            <BaseControl
              help={__('Select the sections in which to publish this article. If none are selected, it will be published to the default section.', 'apple-news')}
            >
              {sections.map(({ id, name }) => (
                <CheckboxControl
                  checked={selectedSections.includes(id)}
                  key={id}
                  label={name}
                  onChange={() => onChangeSelectedSections(id)}
                />
              ))}
            </BaseControl>
          ) : null}
        </>
      )}
    </PanelBody>
  );
}

Sections.propTypes = {
  autoAssignCategories: PropTypes.bool.isRequired,
  automaticAssignment: PropTypes.bool.isRequired,
  onChangeAutoAssignCategories: PropTypes.func.isRequired,
  onChangeSelectedSections: PropTypes.func.isRequired,
  sections: PropTypes.arrayOf(PropTypes.shape(SECTION_SHAPE)).isRequired,
  selectedSections: PropTypes.arrayOf(PropTypes.string).isRequired,
};

export default Sections;
