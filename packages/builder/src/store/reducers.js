import { combineReducers } from 'redux';
import { form } from '../reducers/form';
import { extra } from '../reducers/extra';
import { status } from '../components/status/reducers';

const rootReducer = combineReducers({
  form,
  status,
  extra,
});

export default rootReducer;
