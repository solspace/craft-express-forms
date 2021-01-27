import PropTypes from 'prop-types';
import React from 'react';
import translate from '../../../../functions/translator';
import BaseInput from './BaseInput';

class Select extends BaseInput {
  static propTypes = {
    ...BaseInput.propTypes,
    emptyOption: PropTypes.string,
    options: PropTypes.arrayOf(
      PropTypes.shape({
        value: PropTypes.node,
        label: PropTypes.node,
      })
    ),
  };

  renderInput() {
    const { name, value, emptyOption, saveHandler, options = [], disabled = false } = this.props;

    return (
      <div className="select fullwidth">
        <select name={name} value={value} onChange={saveHandler} disabled={disabled}>
          {emptyOption && <option value="">{translate(emptyOption)}</option>}
          {options.map((item) => (
            <option value={item.value} key={item.value}>
              {translate(item.label)}
            </option>
          ))}
        </select>
      </div>
    );
  }
}

export default Select;
