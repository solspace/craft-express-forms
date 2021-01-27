import React from 'react';
import PropTypes from 'prop-types';
import Info from '@xf/builder/components/common/info/Info';
import translate from '@xf/builder/functions/translator';

const Block = ({ title, description, extraBlock, children }) => (
  <div className="block">
    <h3>
      {translate(title)} <Info description={translate(description)} />
      {extraBlock}
    </h3>
    {children}
  </div>
);

Block.propTypes = {
  title: PropTypes.string.isRequired,
  description: PropTypes.string,
  extraBlock: PropTypes.object,
};

export default Block;
