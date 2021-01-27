import { shallow } from 'enzyme';
import React from 'react';
import sinon from 'sinon';
import FieldEnhanced from '../fieldlist/FieldEnhanced';

describe('<Field />', () => {
  it('renders & displays correctly', () => {
    const wrapper = shallow(<FieldEnhanced name="Field" handle="field" type="text" />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders & displays correctly with empty name and handle', () => {
    const wrapper = shallow(<FieldEnhanced type="text" />);
    expect(wrapper).toMatchSnapshot();
  });

  it('changes name', () => {
    const onChange = jest.fn();
    const wrapper = shallow(<FieldEnhanced name="Field" handle="field" type="text" nameHandler={onChange} />);

    wrapper.find('input.name').simulate('change', 'test');
    expect(onChange).toBeCalledWith('test');
  });

  it('changes handle', () => {
    const onChange = jest.fn();
    const wrapper = shallow(<FieldEnhanced name="Field" handle="field" type="text" handleHandler={onChange} />);

    wrapper.find('input.handle').simulate('change', 'test');
    expect(onChange).toBeCalledWith('test');
  });

  it('shows extras for File type fields', () => {
    const wrapper = shallow(<FieldEnhanced name="Field" handle="field" type="file" />);
    expect(wrapper).toMatchSnapshot();
    expect(wrapper.find('Connect(FileExtras)')).toHaveLength(1);
  });

  describe('confirm dialog', () => {
    it('opens a confirm dialog on delete', () => {
      const confirm = sinon.spy();
      global.confirm = confirm;
      const wrapper = shallow(<FieldEnhanced name="Field" handle="field" type="text" />);

      wrapper.find('a.delete').simulate('click');
      expect(confirm.called);
      global.confirm = undefined;
    });

    it('calls the removeHandler if confirmed', () => {
      const confirm = sinon.fake.returns(true);
      const removeHandler = sinon.spy();

      global.confirm = confirm;
      const wrapper = shallow(<FieldEnhanced name="Field" handle="field" type="text" removeHandler={removeHandler} />);

      wrapper.find('a.delete').simulate('click');
      expect(confirm.called).toBeTruthy();
      expect(removeHandler.called).toBeTruthy();
      global.confirm = undefined;
    });

    it('does not call the removeHandler if not confirmed', () => {
      const confirm = sinon.fake.returns(false);
      const removeHandler = sinon.spy();

      global.confirm = confirm;
      const wrapper = shallow(<FieldEnhanced name="Field" handle="field" type="text" removeHandler={removeHandler} />);

      wrapper.find('a.delete').simulate('click');
      expect(confirm.called).toBeTruthy();
      expect(removeHandler.called).toBeFalsy();
      global.confirm = undefined;
    });
  });
});
