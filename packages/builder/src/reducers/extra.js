// Reducer
// ==================
import { refreshIntegrationResourcesUrl } from '../config';

const START_FETCHING_RESOURCES = 'START_FETCHING_RESOURCES';
const STOP_FETCHING_RESOURCES = 'STOP_FETCHING_RESOURCES';
const SET_RESOURCES = 'SET_RESOURCES';

export const refreshResourcesAction = (handle) => (dispatch) => {
  dispatch({ type: START_FETCHING_RESOURCES });
  fetch(refreshIntegrationResourcesUrl, {
    method: 'post',
    cache: 'no-cache',
    credentials: 'same-origin',
    headers: {
      'X-CSRF-Token': Craft.csrfTokenValue,
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ handle }),
  })
    .then((data) => data.json())
    .then((data) => {
      dispatch({ type: SET_RESOURCES, handle, resources: data.resources });
      dispatch({ type: STOP_FETCHING_RESOURCES });
    })
    .catch(() => dispatch({ type: STOP_FETCHING_RESOURCES }));
};

const defaultState = {
  fileKinds: [],
  volumes: {},
  notifications: [],
  isUpdatingIntegrations: false,
  integrations: [],
  enhancedUi: true,
  isPro: false,
};

export const extra = (state = defaultState, action) => {
  switch (action.type) {
    case START_FETCHING_RESOURCES:
      return { ...state, isUpdatingIntegrations: true };

    case STOP_FETCHING_RESOURCES:
      return { ...state, isUpdatingIntegrations: false };

    case SET_RESOURCES:
      const { handle, resources } = action;
      const index = state.integrations.findIndex((item) => item.handle === handle);

      if (index === -1) {
        return state;
      }

      const clone = { ...state.integrations[index], resources };

      return {
        ...state,
        integrations: [...state.integrations.slice(0, index), clone, ...state.integrations.slice(index + 1)],
      };

    default:
      return state;
  }
};
