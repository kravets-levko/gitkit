'use strict';

window.$ = window.jQuery = require('jquery');
window.Popper = require('popper.js');
require('bootstrap');

// Dropdown: allow to stay visible if click inside and close on click outside
$('body').on('hide.bs.dropdown', '.dropdown[data-close-outside]', function(event) {
  const nodes = document.querySelectorAll(':hover');
  const elementUnderCursor = nodes[nodes.length - 1];
  const list = this.querySelector('.dropdown-menu');
  if (list.contains(elementUnderCursor)) {
    event.preventDefault();
    event.returnValue = false;
    return false;
  }
});
