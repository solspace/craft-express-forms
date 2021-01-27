import React from 'react';
import FieldListContainer from './components/fields/FieldListContainer';
import SettingsContainer from './components/settings/SettingsContainer';
import SaveContainer from './components/status/SaveContainer';
import './app.styl';

const App = () => (
  <>
    <SaveContainer />
    <SettingsContainer />
    <FieldListContainer />
  </>
);

export default App;
