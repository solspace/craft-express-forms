export const getNumberOrNull = (value) => {
  if (typeof value === undefined || value === null) {
    value = '';
  }

  value = value + '';
  value = value.replace(/[^0-9.]/g, '');

  if (value === '') {
    return null;
  }

  value = parseFloat(value);

  return value;
};

export const createOption = (label, value) => ({
  label,
  value,
});
