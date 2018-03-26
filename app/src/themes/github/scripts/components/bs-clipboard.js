import $ from 'jquery';
import Clipboard from 'clipboard';
import Widget from './widget';

export default class BSClipboard extends Widget {
  static selector() {
    return '[bs-clipboard]';
  }

  init() {
    const clipboard = new Clipboard(this.$node.get(0));
    clipboard.on('success', function(element) {
      // TODO: Move it from here, it's custom code
      element = $(element.trigger);
      const originalTitle = element.attr('data-original-title');
      element.attr('data-original-title', element.attr('data-title-copied'));
      element.tooltip('setContent').tooltip('show');
      element.one('hidden.bs.tooltip', function() {
        element.attr('data-original-title', originalTitle);
      });
    });

    return false;
  }
}
