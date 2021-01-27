import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import styled from 'styled-components';
import { translate } from '../../../../functions/translator';
import { getNumberOrNull } from '../../../../functions/utility';
import ToggleSwitch from '../../../common/lightswitch/ToggleSwitch';
import { Select, Text } from '../../../settings/block/inputs';

const FieldExtras = styled.div`
  padding: 10px 10px 0;
`;

const FileProperties = styled.div`
  display: grid;
  grid-template-columns: auto 100px 35px 120px auto;
  grid-column-gap: 5px;
  align-items: center;
  justify-items: end;
`;

const FileTypesRestrictor = styled.div`
  display: flex;
  justify-content: flex-start;

  margin-top: 10px;

  > div {
    margin-left: 10px;
  }
`;

const FileTypes = styled.ul`
  margin-top: 10px;
  column-count: 4;

  > li {
    input {
      display: inline-block;
      margin-right: 5px;
    }
  }
`;

const mapStateToProps = (state) => ({
  allFileKinds: state.extra.fileKinds,
  volumes: state.extra.volumes,
});

const FileExtras = ({
  restrictFileKinds = false,
  fileKinds = [],
  allFileKinds = [],
  volumes = [],
  volumeId = '',
  maxFileSizeKB = '',
  fileCount = '',
  propChangeHandler,
}) => (
  <FieldExtras>
    <FileProperties>
      <Select
        emptyOption={translate('Select upload location...')}
        options={volumes}
        value={volumeId ?? ''}
        saveHandler={(event) => propChangeHandler('volumeId', getNumberOrNull(event.target.value))}
      />

      <span>{translate('Max file count')}</span>
      <Text
        value={fileCount ?? ''}
        saveHandler={(event) => propChangeHandler('fileCount', getNumberOrNull(event.target.value))}
      />

      <span>{translate('Max file size (KB)')}</span>
      <Text
        value={maxFileSizeKB ?? ''}
        saveHandler={(event) => propChangeHandler('maxFileSizeKB', getNumberOrNull(event.target.value))}
      />
    </FileProperties>

    <FileTypesRestrictor>
      <label>{translate('Restrict file types')}</label>
      <div>
        <ToggleSwitch
          value={restrictFileKinds}
          toggleHandler={() => propChangeHandler('restrictFileKinds', !restrictFileKinds)}
        />
      </div>
    </FileTypesRestrictor>

    {restrictFileKinds && (
      <FileTypes>
        {allFileKinds.map((item, i) => (
          <li key={i}>
            <label>
              <input
                type="checkbox"
                value={item}
                checked={fileKinds.includes(item)}
                onChange={(event) => {
                  const { value, checked } = event.target;
                  const existingValues = [...fileKinds];
                  if (checked && !existingValues.includes(value)) {
                    existingValues.push(value);
                  } else if (!checked && existingValues.includes(value)) {
                    existingValues.splice(existingValues.indexOf(value), 1);
                  }

                  propChangeHandler('fileKinds', existingValues);
                }}
              />
              {item.substr(0, 1).toUpperCase() + item.substr(1).toLowerCase()}
            </label>
          </li>
        ))}
      </FileTypes>
    )}
  </FieldExtras>
);

FileExtras.propTypes = {
  fileKinds: PropTypes.array,
  selectedFileKinds: PropTypes.array,
  volumes: PropTypes.array,
  volumeId: PropTypes.number,
  maxFileSizeKB: PropTypes.number,
  fileCount: PropTypes.number,
  propChangeHandler: PropTypes.func,
};

export default connect(mapStateToProps)(FileExtras);
