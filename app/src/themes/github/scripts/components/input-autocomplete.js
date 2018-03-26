import $ from 'jquery';
import { isString, map, each } from 'lodash';
import Widget from './widget';

export default class InputAutocomplete extends Widget {
  static selector() {
    return '[is="input-autocomplete"]';
  }

  getMenuItems() {
    let result = [];
    const listId = this.$input.attr('list');
    if (isString(listId) && (listId !== '')) {
      const list = document.getElementById(listId);
      if (list) {
        result = map(list.options, option => ({
          content: option.innerHTML,
          value: option.value,
        }));
      }
    }
    return result;
  }

  renderMenu() {
    const items = this.getMenuItems();
    if (items.length > 0) {
      const result = $('<div>').addClass('dropdown-menu w-100');
      each(items, item => {
        const menuItem = $('<a>')
          .attr('href', 'javascript:void(0)')
          .addClass('dropdown-item')
          .html(item.content)
          .on('click', () => { this.setValue(item.value); });
        result.append(menuItem);
      });
      return result;
    }
    return null;
  }

  setValue(value) {
    if (this.$input) {
      this.$input.val(value);
    }
  }

  render() {
    this.$input = this.$node.clone();

    const result = $('<div>')
      .addClass('dropdown position-relative')
      .attr('tabindex', '-1')
      .on('focus', () => {
         this.$input.focus();
      });

    result.append(this.$input);
    const menu = this.renderMenu();
    if (menu) {
      this.$input.attr('data-toggle', 'dropdown').removeAttr('list');
      result.append(menu);
    }

    return result;
  }
}
