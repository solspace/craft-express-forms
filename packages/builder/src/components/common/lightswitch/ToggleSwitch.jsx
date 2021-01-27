import React from 'react';
import PropTypes from 'prop-types';
import './toggleSwitch.styl';

const ToggleSwitch = ({ value = false, toggleHandler }) => (
  <div
    className={`toggle-switch ${value ? 'on' : 'off'}`}
    onClick={toggleHandler}
    aria-checked={!!value}
    role="checkbox"
  >
    <div className="toggle-switch-handle" />
  </div>
);

ToggleSwitch.propTypes = {
  value: PropTypes.bool,
  toggleHandler: PropTypes.func,
};

export default ToggleSwitch;
