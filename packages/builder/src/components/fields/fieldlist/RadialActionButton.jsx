import PropTypes from 'prop-types';
import React, { useState } from 'react';
import translate from '../../../functions/translator';
import types from '../types';
import './radialActionButton.styl';
import text from './icons/fieldtype-text.svg';
import textarea from './icons/fieldtype-textarea.svg';
import checkbox from './icons/fieldtype-checkbox.svg';
import email from './icons/fieldtype-email.svg';
import file from './icons/fieldtype-file.svg';
import hidden from './icons/fieldtype-hidden.svg';
import options from './icons/fieldtype-options.svg';

const icons = { text, textarea, checkbox, email, file, hidden, options };

const RadialActionButton = ({ type, changeTypeHandler }) => {
  const [isOpen, setOpen] = useState(false);

  const getIcon = (type) => {
    const IconComponent = icons[type];

    return <IconComponent />;
  };

  return (
    <>
      {isOpen && <div className="radial-action-button-overlay" onClick={() => setOpen(false)} />}
      <div className={`radial-action-button${isOpen ? ' open' : ''}`}>
        <ul className={`items data-type-count-${types.length - 1}`}>
          {types.map(
            (itemType) =>
              itemType !== type && (
                <li key={itemType}>
                  <a
                    onClick={() => {
                      changeTypeHandler(itemType);
                      setOpen(false);
                    }}
                    data-field-type={itemType}
                    title={translate(itemType[0].toUpperCase() + itemType.slice(1))}
                    tabIndex={-1}
                  >
                    <span>{getIcon(itemType)}</span>
                  </a>
                </li>
              )
          )}
        </ul>

        <button className="action-button" tabIndex={-1} onClick={() => setOpen(true)}>
          <span className="type-icon">{getIcon(type)}</span>
        </button>

        <div className="close-button" onClick={() => setOpen(false)} />

        <div className="backdrop" />
      </div>
    </>
  );
};

RadialActionButton.propTypes = {
  type: PropTypes.oneOf(types).isRequired,
  changeTypeHandler: PropTypes.func,
};

export default RadialActionButton;
