import React from 'react';
import PropTypes from 'prop-types';
import ToggleSwitch from '../../../common/lightswitch/ToggleSwitch';
import BaseInput from './BaseInput';

class Checkbox extends BaseInput {
  static propTypes = {
    ...BaseInput.propTypes,
    value: PropTypes.bool,
  };

  toggleHandler = () => {
    const { name, value, saveHandler } = this.props;

    saveHandler({
      target: {
        name,
        value: !value,
      },
    });
  };

  renderInput() {
    const { value } = this.props;

    return <ToggleSwitch value={!!value} toggleHandler={this.toggleHandler} />;
  }
}

export default Checkbox;
