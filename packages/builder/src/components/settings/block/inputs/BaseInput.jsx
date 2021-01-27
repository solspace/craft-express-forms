import Tooltip from '@material-ui/core/Tooltip/Tooltip';
import React from 'react';
import PropTypes from 'prop-types';
import translate from '../../../../functions/translator';

class BaseInput extends React.Component {
  static propTypes = {
    label: PropTypes.string,
    description: PropTypes.string,
    required: PropTypes.bool,
    name: PropTypes.string,
    value: PropTypes.any,
    saveHandler: PropTypes.func,
    valueToCamelCase: PropTypes.bool,
    disabled: PropTypes.bool,
  };

  static defaultProps = {
    description: null,
    required: false,
  };

  getBlockClasses() {
    return [];
  }

  renderInput() {}

  render() {
    const { label, required, description } = this.props;

    const blockClasses = ['block-row', ...this.getBlockClasses()];

    return (
      <div className={blockClasses.join(' ')}>
        {label && (
          <div className="label">
            <Tooltip title={translate(description) ?? ''} placement="bottom">
              <label className={required ? 'required' : ''}>{translate(label)}</label>
            </Tooltip>
          </div>
        )}
        <div className="input ltr">{this.renderInput()}</div>
      </div>
    );
  }
}

export default BaseInput;
