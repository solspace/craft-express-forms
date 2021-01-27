import { translate, replace } from '../translator';

describe('translator functionality', () => {
  it('translates if Craft is present', () => {
    global.Craft = { t: jest.fn().mockReturnValue('french translation') };

    const translation = translate('test {replaceMe} message', {
      replaceMe: 'bingo',
    });

    expect(global.Craft.t).toHaveBeenCalledWith('express-forms', 'test {replaceMe} message', { replaceMe: 'bingo' });
    expect(translation).toEqual('french translation');
  });

  it('falls back to a simple replacer if Craft not present', () => {
    if (typeof global.Craft !== 'undefined') {
      delete global.Craft;
    }

    const translation = translate('test {replaceMe} message', {
      replaceMe: 'bingo',
    });

    expect(translation).toEqual('test bingo message');
  });
});

describe('Replacer functionality', () => {
  it('replaces values correctly', () => {
    const replacement = replace('test {one}, {two}, {three} to be replaced, but not {four}', {
      one: 1,
      two: 'two',
      three: 'DREI',
    });

    expect(replacement).toEqual('test 1, two, DREI to be replaced, but not {four}');
  });
});
