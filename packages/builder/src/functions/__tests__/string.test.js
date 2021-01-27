import { toCamelCase } from '../string';

describe('string manipulation library', () => {
  describe('toCamelCase()', () => {
    it('converts a basic string', () => {
      expect(toCamelCase('A basic string')).toEqual('aBasicString');
    });

    it('converts with numbers', () => {
      expect(toCamelCase('A basic 22 string1')).toEqual('aBasic22String1');
    });

    it('removes special chars', () => {
      expect(toCamelCase('A basic [{()}]$€-=!?@#&*%+^_"\'/ string')).toEqual('aBasicString');
    });

    it('leaves uppercase letters as they should be', () => {
      expect(toCamelCase('A BASIC STRING')).toEqual('aBASICSTRING');
    });
  });
});
