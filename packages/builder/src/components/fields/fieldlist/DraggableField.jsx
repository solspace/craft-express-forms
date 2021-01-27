import React from 'react';
import PropTypes from 'prop-types';
import { Draggable } from 'react-beautiful-dnd';
import FieldEnhanced from './FieldEnhanced';
import FieldBasic from './FieldBasic';

const getFieldElement = (isEnhanced, props, dragHandleProps) => {
  if (isEnhanced) {
    return <FieldEnhanced {...props} dragHandleProps={dragHandleProps} />;
  }

  return <FieldBasic {...props} dragHandleProps={dragHandleProps} />;
};

const DraggableField = ({ uid, index, enhancedUi, ...fieldProps }) => (
  <Draggable draggableId={`field-${uid}`} index={index}>
    {(provided, snapshot) => (
      <li className="draggable-field" ref={provided.innerRef} {...provided.draggableProps}>
        {getFieldElement(enhancedUi, fieldProps, provided.dragHandleProps)}
      </li>
    )}
  </Draggable>
);

DraggableField.propTypes = {
  uid: PropTypes.string.isRequired,
  index: PropTypes.number.isRequired,
  enhancedUi: PropTypes.bool,
};

export default DraggableField;
