import PropTypes from 'prop-types';
import React from 'react';
import { SketchPicker } from 'react-color';
import styled from 'styled-components';
import BaseInput from './BaseInput';

const initialState = {
  displayColorPicker: false,
};

const PreviewWrapper = styled.div`
  padding: 5px;
  background: #fff;
  border-radius: 1px;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
  display: inline-block;
  cursor: pointer;
`;

const Preview = styled.div`
  width: 36px;
  height: 14px;
  border-radius: 2px;
`;

const CoverWrapper = styled.div`
  position: absolute;
  z-index: 2;
`;

const Cover = styled.div`
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
`;

class Color extends BaseInput {
  static propTypes = {
    ...BaseInput.propTypes,
    value: PropTypes.string,
  };

  constructor(props) {
    super(props);

    this.state = initialState;
  }

  renderInput() {
    const { value, saveHandler } = this.props;
    const { displayColorPicker } = this.state;

    return (
      <div>
        <PreviewWrapper onClick={() => this.setState({ displayColorPicker: true })}>
          <Preview style={{ backgroundColor: value }} />
        </PreviewWrapper>

        {displayColorPicker && (
          <CoverWrapper>
            <Cover onClick={() => this.setState({ displayColorPicker: false })} />

            <SketchPicker color={value} onChange={saveHandler} disableAlpha={true} />
          </CoverWrapper>
        )}
      </div>
    );
  }
}

export default Color;
