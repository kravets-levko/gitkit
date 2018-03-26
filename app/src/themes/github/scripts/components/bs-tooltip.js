import Widget from './widget';

export default class BSTooltip extends Widget {
  static selector() {
    return '[bs-tooltip]';
  }

  init() {
    this.$node.tooltip();
    return false;
  }
}
