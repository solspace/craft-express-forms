import React from 'react';
import BaseInput from './BaseInput';

class Placeholder extends BaseInput {
  renderInput() {
    return this.props.value;
  }
}

export default Placeholder;
