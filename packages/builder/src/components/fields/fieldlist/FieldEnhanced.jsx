import PropTypes from 'prop-types';
import React from 'react';
import translate from '../../../functions/translator';
import ToggleSwitch from '../../common/lightswitch/ToggleSwitch';
import FileExtras from './extras/FileExtras';
import RadialActionButton from './RadialActionButton';
import { FILE } from '../types';

const FieldEnhanced = ({
  type,
  name,
  handle,
  required,
  nameHandler,
  handleHandler,
  customFieldPropertyHandler,
  requiredHandler,
  typeHandler,
  removeHandler,
  dragHandleProps,
  ...extraProps
}) => (
  <>
    <div className="base">
      <div className="main-row">
        <div className="icon">
          <RadialActionButton type={type} changeTypeHandler={typeHandler} />
        </div>
        <div className="data">
          <div>
            <input
              className={`name ${name ? 'has-content' : ''}`}
              type="text"
              value={name ?? ''}
              onChange={nameHandler}
              autoComplete="off"
            />
            <label>{translate('Name')}</label>
            <span className="focus-border" />
          </div>
          <div>
            <input
              className={`handle ${handle ? 'has-content' : ''}`}
              type="text"
              value={handle ?? ''}
              onChange={handleHandler}
              autoComplete="off"
            />
            <label>{translate('Handle')}</label>
            <span className="focus-border" />
          </div>
        </div>
        <div className="attributes">
          <div>
            <label>{translate('Required')}</label>
            <div>
              <ToggleSwitch value={required} toggleHandler={requiredHandler} />
            </div>
          </div>
        </div>
      </div>

      {type === FILE && <FileExtras {...extraProps} propChangeHandler={customFieldPropertyHandler} />}
    </div>
    <div className="actions">
      <a
        className="icon delete"
        onClick={() => {
          const confirmMessage = 'Are you sure you want to remove this field?';
          if (confirm(translate(confirmMessage))) {
            removeHandler();
          }
        }}
        tabIndex={-1}
      />
      <a className="icon move" {...dragHandleProps} tabIndex={-1} />
    </div>
  </>
);

FieldEnhanced.propTypes = {
  type: PropTypes.string.isRequired,
  name: PropTypes.string,
  handle: PropTypes.string,
  required: PropTypes.bool,
  nameHandler: PropTypes.func,
  handleHandler: PropTypes.func,
  requiredHandler: PropTypes.func,
  typeHandler: PropTypes.func,
  removeHandler: PropTypes.func,
  customFieldPropertyHandler: PropTypes.func,
  dragHandleProps: PropTypes.object,
};

export default FieldEnhanced;
