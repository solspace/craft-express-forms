import { v4 as uuid } from 'uuid';
import { TEXT } from '../components/fields/types';

// Update
// ==================
export const ADD_FIELD = 'ADD_FIELD';
export const REMOVE_FIELD = 'REMOVE_FIELD';
export const EDIT_FIELD = 'EDIT_FIELD';
export const MOVE_FIELD = 'MOVE_FIELD';

const defaultField = {
  id: null,
  uid: null,
  type: TEXT,
  name: '',
  handle: '',
  required: false,
};

export const addField = () => ({
  type: ADD_FIELD,
});

export const removeField = (index) => ({
  type: REMOVE_FIELD,
  index,
});

export const editField = (index, key, value) => ({
  type: EDIT_FIELD,
  index,
  key,
  value,
});

export const moveField = (fromIndex, toIndex) => ({
  type: MOVE_FIELD,
  fromIndex,
  toIndex,
});

const addFieldReducer = (state) => [...state, { ...defaultField, uid: uuid() }];

const removeFieldReducer = (state, { index }) => [...state.slice(0, index), ...state.slice(index + 1)];

const editFieldReducer = (state, { index, key, value }) => {
  const clone = [...state];
  clone[index] = {
    ...clone[index],
    [key]: value,
  };

  return clone;
};

const moveFieldReducer = (state, { fromIndex, toIndex }) => {
  const clone = [...state.slice(0, fromIndex), ...state.slice(fromIndex + 1)];
  const item = { ...state[fromIndex] };

  return [...clone.slice(0, toIndex), item, ...clone.slice(toIndex)];
};

// Reducer
// ==================

const defaultState = [];

export const fields = (state = defaultState, action) => {
  switch (action.type) {
    case ADD_FIELD:
      return addFieldReducer(state);

    case REMOVE_FIELD:
      return removeFieldReducer(state, action);

    case EDIT_FIELD:
      return editFieldReducer(state, action);

    case MOVE_FIELD:
      return moveFieldReducer(state, action);
  }

  return state;
};
