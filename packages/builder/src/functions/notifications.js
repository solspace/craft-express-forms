export const error = (message) => {
  if (typeof Craft !== 'undefined') {
    for (const msg of getArray(message)) {
      Craft.cp.displayError(msg);
    }
  } else {
    console.error(message);
  }
};

export const notice = (message) => {
  if (typeof Craft !== 'undefined') {
    for (const msg of getArray(message)) {
      Craft.cp.displayNotice(msg);
    }
  } else {
    console.log(message);
  }
};

const getArray = (val) => (Array.isArray(val) ? val : [val]);
