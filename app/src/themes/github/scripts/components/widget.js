import $ from 'jquery';

export default class Widget {
  static selector() {
    return null;
  }

  static bootstrap() {
    $(this.selector()).each((index, node) => {
      new this(node);
    });
  }

  render() {
    return this.$node;
  }

  mount($component) {
    this.$node.replaceWith($component);
    this.$node = $component;
  }

  init() {
    this.$attributes = this.$node.prop('attributes');

    this.$slots = {};
    this.$node.find('slot').each((index, slot) => {
      const $slot = $(slot);
      const name = $slot.attr('name') || 'default';
      this.$slots[name] = this.$slots[name] || [];
      this.$slots[name].push($slot.detach());
    });

    const name = 'default';
    this.$slots[name] = this.$slots[name] || [];
    this.$slots[name].push(this.$node);

    return true;
  }

  constructor(node) {
    this.$node = $(node);

    if (this.init()) {
      const $component = this.render();
      this.mount($component);
    }
  }
}
