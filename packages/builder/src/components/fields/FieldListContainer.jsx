import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import translate from '../../functions/translator';
import FieldList from './fieldlist/FieldList';
import './fields.styl';
import { addField, editField, removeField, moveField } from '../../reducers/fields';

const mapStateToProps = (state) => ({
  fields: state.form.fields,
  enhancedUi: state.extra.enhancedUi,
});

const mapDispatchToProps = (dispatch) => ({
  addField: () => dispatch(addField()),
  editField: (index, key, value) => dispatch(editField(index, key, value)),
  removeField: (index) => dispatch(removeField(index)),
  moveField: (fromIndex, toIndex) => dispatch(moveField(fromIndex, toIndex)),
});

class FieldListContainer extends React.Component {
  static propTypes = {
    enhancedUi: PropTypes.bool,
    fields: PropTypes.array,
    addField: PropTypes.func,
    editField: PropTypes.func,
    removeField: PropTypes.func,
    moveField: PropTypes.func,
  };

  render() {
    const { fields = [], addField, editField, removeField, moveField, enhancedUi } = this.props;

    const listProps = {
      fields,
      addField,
      editField,
      removeField,
      moveField,
      enhancedUi,
    };

    return (
      <div className="fields">
        <h3>{translate('Fields & Layout')}</h3>

        <FieldList {...listProps} />

        <button className="btn submit add icon" onClick={addField}>
          {translate('Add field')}
        </button>
      </div>
    );
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(FieldListContainer);
