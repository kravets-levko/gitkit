import $ from 'jquery';
import { each } from 'lodash';
import Widget from './widget';
import markdown from '../services/markdown';

export default class Markdown extends Widget {
  static selector() {
    return 'markdown';
  }

  render() {
    const result = $('<div>');

    each(this.$attributes, (attr) => {
      result.attr(attr.name, attr.value);
    });

    return result.addClass('markdown')
      .html(markdown(this.$slots.default.map($node => $node.text()).join('\n')));
  }
}
