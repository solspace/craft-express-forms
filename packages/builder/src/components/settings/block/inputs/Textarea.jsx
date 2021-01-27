import React from 'react';
import BaseInput from './BaseInput';

class Textarea extends BaseInput {
  renderInput() {
    const { name, value, saveHandler } = this.props;

    return <textarea className="text nicetext" name={name} onChange={saveHandler} defaultValue={value} />;
  }
}

export default Textarea;
