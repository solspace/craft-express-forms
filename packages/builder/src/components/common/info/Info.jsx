import React from 'react';
import './info.styl';
import IconInfo from './info-circle-solid.svg';
import Tooltip from '@material-ui/core/Tooltip';
import translate from '@xf/builder/functions/translator';

const Info = ({ description }) =>
  description ? (
    <Tooltip title={translate(description)} placement="right">
      <span className="information-dot">
        <IconInfo />
      </span>
    </Tooltip>
  ) : null;

export default Info;
