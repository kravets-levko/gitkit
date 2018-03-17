import $ from 'jquery';
import Popper from 'popper.js';
import Vue from 'vue';
const Clipboard = require('clipboard');

window.$ = window.jQuery = $;
window.Popper = Popper;
import 'bootstrap';

$(() => {
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
});

Vue.directive('bsTooltip', {
  bind(element) {
    $(element).tooltip();
  },
  unbind(element) {
    $(element).tooltip('dispose');
  },
});

Vue.directive('bsClipboard', {
  bind(element) {
    const clipboard = new Clipboard(element);
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
    $(element).data('bs-clipboard', clipboard);
  },
  unbind(element) {
    const clipboard = $(element).data('bs-clipboard');
    if (clipboard instanceof Clipboard) {
      clipboard.destroy();
    }
  },
});

// TODO: Refactor
$(function() {
  $('body').on('click', '.collapsed-list', function() {
    $(this).removeClass('collapsed-list');
  });

  // Settings page: default branch select
  $('#default-branch-select').on('click', '.dropdown-menu a', function() {
    const branchName = $(this).text();
    const dropdown = $('#default-branch-select');
    dropdown.find('input').val(branchName);
    dropdown.find('button').text(branchName);
  });
});
