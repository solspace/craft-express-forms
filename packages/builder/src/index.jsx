import React from 'react';
import ReactDOM from 'react-dom';
import BuilderApp from './App';

import { Provider } from 'react-redux';
import configureStore from './store/configureStore';

const store = configureStore({
  form: expressForm,
  extra: {
    fileKinds,
    volumes,
    notifications,
    integrations,
    enhancedUi,
    isPro,
  },
});

ReactDOM.render(
  <Provider store={store}>
    <BuilderApp />
  </Provider>,
  document.getElementById('builder-app')
);
