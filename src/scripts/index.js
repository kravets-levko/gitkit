'use strict';

window.$ = window.jQuery = require('jquery');
window.Popper = require('popper.js');
require('bootstrap');
const Clipboard = require('clipboard');
const marked = require('marked');

// Tooltips
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});

// Copy to clipboard buttons
$(function() {
  const $body = $('body');

  const clipboard = new Clipboard('[data-clipboard-target], [data-clipboard-text]');
  clipboard.on('success', function(element) {
    element = $(element.trigger);
    const originalTitle = element.attr('data-original-title');
    element.attr('data-original-title', element.attr('data-title-copied'));
    element.tooltip('setContent').tooltip('show');
    element.one('hidden.bs.tooltip', function() {
      element.attr('data-original-title', originalTitle);
    });
  });

  // Dropdown: allow to stay visible if click inside and close on click outside
  $body.on('hide.bs.dropdown', '.dropdown[data-close-outside]', function(event) {
    const nodes = document.querySelectorAll(':hover');
    const elementUnderCursor = nodes[nodes.length - 1];
    const list = this.querySelector('.dropdown-menu');
    if (list.contains(elementUnderCursor)) {
      event.preventDefault();
      event.returnValue = false;
      return false;
    }
  });

  $body.on('click', '.collapsed-list', function() {
    $(this).removeClass('collapsed-list');
  });

  // Markdown
  const renderer = new marked.Renderer();
  const options = {
    renderer: renderer,
    gfm: true,
    tables: true,
    breaks: false,
    pedantic: false,
    sanitize: false,
    smartLists: true,
    smartypants: false
  };

  renderer.listitem = function(text) {
    if (/^\s*\[[x ]\]\s*/.test(text)) {
      text = text
        .replace(
          /^\s*\[ \]\s*/,
          '<input type="checkbox" style="vertical-align: middle; margin: 0 0.2em 0.25em -1.6em; font-size: 16px;" disabled>'
        )
        .replace(
          /^\s*\[x\]\s*/,
          '<input type="checkbox" style="vertical-align: middle; margin: 0 0.2em 0.25em -1.6em; font-size: 16px;" checked disabled>'
        );
      return '<li style="list-style: none">' + text + "</li>";
    } else {
      return '<li>' + text + '</li>';
    }
  };

  $('markdown').each(function() {
    const item = $(this);
    item.replaceWith(marked(item.text(), options));
  });
});
