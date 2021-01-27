import * as reducers from '../fields';

describe('Fields actions', () => {
  it('creates addField action correctly', () => {
    const expectedAction = { type: 'ADD_FIELD' };
    expect(reducers.addField()).toEqual(expectedAction);
  });

  it('creates removeField action correctly', () => {
    const expectedAction = { type: 'REMOVE_FIELD', index: 5 };
    expect(reducers.removeField(5)).toEqual(expectedAction);
  });

  it('creates editField action correctly', () => {
    const expectedAction = {
      type: 'EDIT_FIELD',
      index: 5,
      key: 'name',
      value: 'First Name',
    };
    expect(reducers.editField(5, 'name', 'First Name')).toEqual(expectedAction);
  });

  it('creates moveField action correctly', () => {
    const expectedAction = {
      type: 'MOVE_FIELD',
      fromIndex: 33,
      toIndex: 666,
    };
    expect(reducers.moveField(33, 666)).toEqual(expectedAction);
  });
});

describe('Fields reducers', () => {
  it('sets initial state', () => {
    expect(reducers.fields(undefined, {})).toEqual([]);
  });

  it('sets restored state correctly', () => {
    const previousState = [{ test: 'value' }, { test: 'value' }];

    expect(reducers.fields(previousState, {})).toEqual(previousState);
  });

  it('adds a field', () => {
    const state = reducers.fields([], reducers.addField());
    expect(state).toHaveLength(1);
    expect(state[0].type).toEqual('text');
    expect(state[0].uid).toMatch(/^[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}$/);
  });

  it('removes a field', () => {
    const state = [{ id: 'test' }, { id: 'test 2' }, { id: 'test 3' }];
    const newState = reducers.fields(state, reducers.removeField(1));
    expect(newState).toHaveLength(2);
    expect(newState).toEqual([{ id: 'test' }, { id: 'test 3' }]);
  });

  it('edits an existing field value', () => {
    const state = [{ id: 'test 1' }, { id: 'test 2' }];
    const newState = reducers.fields(state, reducers.editField(1, 'id', 'An edited ID'));

    expect(newState).toHaveLength(2);
    expect(newState).toEqual([{ id: 'test 1' }, { id: 'An edited ID' }]);
  });

  it('edits a new field value', () => {
    const state = [{ id: 'test 1' }, { id: 'test 2' }];
    const newState = reducers.fields(state, reducers.editField(1, 'name', 'A new name added'));

    expect(newState).toHaveLength(2);
    expect(newState).toEqual([{ id: 'test 1' }, { id: 'test 2', name: 'A new name added' }]);
  });

  it('moves a field from 0 to last', () => {
    const state = [{ id: 'test 1' }, { id: 'test 2' }, { id: 'test 3' }, { id: 'test 4' }];
    const newState = reducers.fields(state, reducers.moveField(0, 3));
    expect(newState).toEqual([{ id: 'test 2' }, { id: 'test 3' }, { id: 'test 4' }, { id: 'test 1' }]);
  });

  it('moves a field from middle to start', () => {
    const state = [{ id: 'test 1' }, { id: 'test 2' }, { id: 'test 3' }, { id: 'test 4' }];
    const newState = reducers.fields(state, reducers.moveField(1, 0));
    expect(newState).toEqual([{ id: 'test 2' }, { id: 'test 1' }, { id: 'test 3' }, { id: 'test 4' }]);
  });

  it('moves a field from last to middle', () => {
    const state = [{ id: 'test 1' }, { id: 'test 2' }, { id: 'test 3' }, { id: 'test 4' }];
    const newState = reducers.fields(state, reducers.moveField(3, 1));
    expect(newState).toEqual([{ id: 'test 1' }, { id: 'test 4' }, { id: 'test 2' }, { id: 'test 3' }]);
  });
});
