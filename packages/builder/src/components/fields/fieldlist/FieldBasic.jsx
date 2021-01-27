import PropTypes from 'prop-types';
import React from 'react';
import styled from 'styled-components';
import translate from '../../../functions/translator';
import ToggleSwitch from '../../common/lightswitch/ToggleSwitch';
import types, { FILE } from '../types';
import FileExtras from './extras/FileExtras';

const BaseGrid = styled.div`
  display: grid;
  grid-template-columns: 60% auto;
  column-gap: 10px;
  row-gap: 10px;

  align-items: center;

  padding: 0 10px;

  > div input,
  > div select {
    width: 100%;
    box-sizing: border-box;
  }
`;

const RequireBox = styled.div`
  display: flex;
  justify-content: flex-end;
  align-content: center;
`;

const FieldBasic = ({
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
      <BaseGrid>
        <div>
          <input
            className="text"
            type="text"
            value={name ?? ''}
            onChange={nameHandler}
            autoComplete="off"
            style={{ fontSize: '16px' }}
            title={translate('Name')}
            placeholder={translate('Name')}
          />
        </div>
        <div>
          <div className="select fullwidth">
            <select
              name="type"
              value={type ?? ''}
              onChange={(event) => typeHandler(event.target.value)}
              title={translate('Type')}
            >
              {types.map((item) => (
                <option value={item} key={item}>
                  {item.substr(0, 1).toUpperCase() + item.substr(1).toLowerCase()}
                </option>
              ))}
            </select>
          </div>
        </div>
        <div>
          <input
            className="text code"
            type="text"
            value={handle ?? ''}
            onChange={handleHandler}
            autoComplete="off"
            style={{ fontSize: '12px' }}
            title={translate('Handle')}
            placeholder={translate('Handle')}
          />
        </div>
        <RequireBox>
          <label style={{ marginRight: 10 }}>{translate('Required')}</label>
          <ToggleSwitch value={required} toggleHandler={requiredHandler} />
        </RequireBox>
      </BaseGrid>

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

FieldBasic.propTypes = {
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

export default FieldBasic;
