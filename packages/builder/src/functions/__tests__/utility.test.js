import { getNumberOrNull } from '../utility';

describe('utility functions', () => {
  describe('getNumberOrEmptyString()', () => {
    it('empty string on null', () => {
      expect(getNumberOrNull(null)).toStrictEqual(null);
    });

    it('empty string on empty string', () => {
      expect(getNumberOrNull('')).toStrictEqual(null);
    });

    it('empty string on empty string with spaces', () => {
      expect(getNumberOrNull('      ')).toStrictEqual(null);
    });

    it('number from number', () => {
      expect(getNumberOrNull(1234)).toStrictEqual(1234);
    });

    it('number from numeric string', () => {
      expect(getNumberOrNull('1234')).toStrictEqual(1234);
    });

    it('number from number with decimals', () => {
      expect(getNumberOrNull(12.34)).toStrictEqual(12.34);
    });

    it('number from numeric string with decimals', () => {
      expect(getNumberOrNull('12.34')).toStrictEqual(12.34);
    });

    it('number from badly formatted number', () => {
      expect(getNumberOrNull('12  3 4')).toStrictEqual(1234);
    });

    it('number from badly formatted number with commas', () => {
      expect(getNumberOrNull('12,  3 4')).toStrictEqual(1234);
    });

    it('number only after first dot', () => {
      expect(getNumberOrNull('1234.56.789')).toStrictEqual(1234.56);
    });
  });
});
