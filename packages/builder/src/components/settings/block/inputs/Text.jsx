import React from 'react';
import BaseInput from './BaseInput';

class Text extends BaseInput {
  renderInput() {
    const { name, value, saveHandler, valueToCamelCase = false } = this.props;

    return (
      <input
        className="text fullwidth"
        type="text"
        name={name}
        value={value}
        onChange={saveHandler}
        data-value-to-camel-case={valueToCamelCase ? '1' : ''}
      />
    );
  }
}

export default Text;
