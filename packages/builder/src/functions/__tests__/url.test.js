import url from '../url';
import sinon from 'sinon';

describe('URL Building functionality', () => {
  it('calls the Craft.getCpUrl() if Craft is globally present', () => {
    const getUrl = sinon.fake.returns('called');
    global.Craft = {
      getCpUrl: getUrl,
    };

    const result = url('/some/url');
    expect(result).toEqual('called');
    expect(getUrl.called).toBeTruthy();

    global.Craft = undefined;
  });

  it("doesn't call Craft.getCpUrl() if Craft is not present globally", () => {
    delete global.Craft;

    const result = url('/some/url');
    expect(result).toEqual('/some/url');
  });

  it('replaces the given values correctly if Craft is not present', () => {
    delete global.Craft;

    const result = url('/some/url/{id}/{slug}', { id: 1, slug: 'abc' });
    expect(result).toEqual('/some/url/1/abc');
  });
});
