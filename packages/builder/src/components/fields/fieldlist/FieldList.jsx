import PropTypes from 'prop-types';
import React from 'react';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';
import { toCamelCase } from '../../../functions/string';
import DraggableField from './DraggableField';

class FieldList extends React.Component {
  static propTypes = {
    fields: PropTypes.array,
    editField: PropTypes.func,
    removeField: PropTypes.func,
    moveField: PropTypes.func,
    enhancedUi: PropTypes.bool,
  };

  onDragEnd = ({ source, destination }) => {
    const { moveField } = this.props;
    moveField(source.index, destination.index);
  };

  render() {
    const { fields, editField, removeField, enhancedUi } = this.props;

    return (
      <DragDropContext onDragEnd={this.onDragEnd}>
        <Droppable droppableId="field-list" type="FIELD">
          {(provided) => (
            <ul className="field-list" ref={provided.innerRef} {...provided.droppableProps}>
              {fields.map((item, index) => (
                <DraggableField
                  key={item.uid}
                  index={index}
                  enhancedUi={enhancedUi}
                  {...item}
                  nameHandler={({ target }) => {
                    editField(index, 'name', target.value);
                    if (!item.id) {
                      editField(index, 'handle', toCamelCase(target.value));
                    }
                  }}
                  handleHandler={({ target }) => editField(index, 'handle', toCamelCase(target.value))}
                  requiredHandler={() => editField(index, 'required', !item.required)}
                  typeHandler={(value) => editField(index, 'type', value)}
                  customFieldPropertyHandler={(property, value) => editField(index, property, value)}
                  removeHandler={() => removeField(index)}
                />
              ))}
            </ul>
          )}
        </Droppable>
      </DragDropContext>
    );
  }
}

export default FieldList;
