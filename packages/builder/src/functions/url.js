import { replace } from './translator';

export const url = (path, variables) => {
  if (typeof Craft !== 'undefined') {
    return Craft.getCpUrl(replace(path, variables));
  }

  return replace(path, variables);
};

export default url;
