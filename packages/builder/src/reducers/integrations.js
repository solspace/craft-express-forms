// Update
// ==================
export const TOGGLE_MAPPING = 'TOGGLE_MAPPING';
export const CHANGE_RESOURCE = 'CHANGE_RESOURCE';
export const UPDATE_FIELD_MAP = 'UPDATE_FIELD_MAP';

export const toggleMapping = (handle) => ({
  type: TOGGLE_MAPPING,
  handle,
});

export const changeResource = (handle, resourceId, fieldMap) => ({
  type: CHANGE_RESOURCE,
  handle,
  resourceId,
  fieldMap,
});

export const updateFieldMap = (handle, resourceFieldId, expressFieldUid) => ({
  type: UPDATE_FIELD_MAP,
  handle,
  resourceFieldId,
  expressFieldUid,
});

const toggleReducer = (state, { handle }) => {
  const clone = { ...state };

  if (clone[handle]) {
    delete clone[handle];

    return clone;
  }

  return {
    ...clone,
    [handle]: { resourceId: '', fieldMap: {} },
  };
};

const changeResourceReducer = (state, { handle, resourceId, fieldMap }) => ({
  ...state,
  [handle]: {
    ...state[handle],
    resourceId,
    fieldMap,
  },
});

const updateFieldMapReducer = (state, { handle, resourceFieldId, expressFieldUid }) => ({
  ...state,
  [handle]: {
    ...state[handle],
    fieldMap: {
      ...state[handle].fieldMap,
      [resourceFieldId]: expressFieldUid,
    },
  },
});

// Reducer
// ==================
const defaultState = {};
export const integrations = (state = defaultState, action) => {
  switch (action.type) {
    case TOGGLE_MAPPING:
      return toggleReducer(state, action);

    case CHANGE_RESOURCE:
      return changeResourceReducer(state, action);

    case UPDATE_FIELD_MAP:
      return updateFieldMapReducer(state, action);
  }

  return state;
};
