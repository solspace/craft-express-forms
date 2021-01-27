import { shallow } from 'enzyme';
import React from 'react';
import Checkbox from '../Checkbox';

describe('<Checkbox />', () => {
  let wrapper;
  let onChange;
  beforeEach(() => {
    onChange = jest.fn();
    wrapper = shallow(
      <Checkbox label="A Test checkbox field" name="test_checkbox" value={true} saveHandler={onChange} />
    );
  });

  it('renders & displays correctly', () => {
    expect(wrapper).toMatchSnapshot();
  });
});
