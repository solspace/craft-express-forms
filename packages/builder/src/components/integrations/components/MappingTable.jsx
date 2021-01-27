import PropTypes from 'prop-types';
import React from 'react';
import styled from 'styled-components';
import { translate } from '../../../functions/translator';

const Select = styled.select`
  padding: 7px 22px 7px 10px;
  background: white;
  border: none;
  border-radius: 0;
  width: 100%;
  height: 32px;
  font-size: 14px;
  line-height: 18px;
  color: #29323d;
  appearance: none;
  white-space: pre;

  &:focus {
    outline: none;
  }
`;

const SelectWrapper = styled.div`
  position: relative;
  background-image: linear-gradient(#fff, #fafafa);
  box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.1);
  white-space: nowrap;

  &:after {
    font-family: 'Craft';
    speak: none;
    font-feature-settings: 'liga', 'dlig';
    text-rendering: optimizeLegibility;
    display: inline-block;
    vertical-align: middle;
    position: absolute;
    z-index: 1;
    top: calc(50% - 5px);
    right: 9px;
    font-size: 10px;
    line-height: 1;
    content: 'downangle';
    user-select: none;
    pointer-events: none;
  }
`;

const MappingTable = ({ title, map, formFields, resourceFields, updateTarget }) => (
  <>
    <table className="shadow-box editable">
      <thead>
        <tr>
          <th>{title}</th>
          <th>Express</th>
        </tr>
      </thead>
      <tbody>
        {resourceFields.map((item) => (
          <tr key={item.handle}>
            <td style={{ textAlign: 'left' }} className={item.required ? 'required' : ''}>
              {item.name}
            </td>
            <td className="textual code" style={{ width: '160px' }}>
              <SelectWrapper>
                <Select
                  value={map[item.handle] ?? ''}
                  onChange={(event) => updateTarget(item.handle, event.target.value)}
                >
                  <option value="">{translate('Select...')}</option>
                  {formFields.map((field) => (
                    <option value={field.value} key={field.value}>
                      {translate(field.label)}
                    </option>
                  ))}
                </Select>
              </SelectWrapper>
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  </>
);

MappingTable.propTypes = {
  title: PropTypes.string,
  map: PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
  formFields: PropTypes.array,
  resourceFields: PropTypes.array,
  updateTarget: PropTypes.func.isRequired,
};

export default MappingTable;
