'use strict';

const $ = require('jquery');
const Clipboard = require('clipboard');
const marked = require('marked');

const hljs = require('highlight.js/lib/highlight');

hljs.registerLanguage('diff', require('highlight.js/lib/languages/diff'));
hljs.registerLanguage('cpp', require('highlight.js/lib/languages/cpp'));
hljs.registerLanguage('xml', require('highlight.js/lib/languages/xml'));
hljs.registerLanguage('bash', require('highlight.js/lib/languages/bash'));
hljs.registerLanguage('coffeescript', require('highlight.js/lib/languages/coffeescript'));
hljs.registerLanguage('css', require('highlight.js/lib/languages/css'));
hljs.registerLanguage('delphi', require('highlight.js/lib/languages/delphi'));
hljs.registerLanguage('dockerfile', require('highlight.js/lib/languages/dockerfile'));
hljs.registerLanguage('ruby', require('highlight.js/lib/languages/ruby'));
hljs.registerLanguage('go', require('highlight.js/lib/languages/go'));
hljs.registerLanguage('ini', require('highlight.js/lib/languages/ini'));
hljs.registerLanguage('java', require('highlight.js/lib/languages/java'));
hljs.registerLanguage('javascript', require('highlight.js/lib/languages/javascript'));
hljs.registerLanguage('json', require('highlight.js/lib/languages/json'));
hljs.registerLanguage('less', require('highlight.js/lib/languages/less'));
hljs.registerLanguage('lua', require('highlight.js/lib/languages/lua'));
hljs.registerLanguage('makefile', require('highlight.js/lib/languages/makefile'));
hljs.registerLanguage('perl', require('highlight.js/lib/languages/perl'));
hljs.registerLanguage('php', require('highlight.js/lib/languages/php'));
hljs.registerLanguage('python', require('highlight.js/lib/languages/python'));
hljs.registerLanguage('scss', require('highlight.js/lib/languages/scss'));
hljs.registerLanguage('sql', require('highlight.js/lib/languages/sql'));
hljs.registerLanguage('yaml', require('highlight.js/lib/languages/yaml'));
hljs.registerLanguage('twig', require('highlight.js/lib/languages/twig'));
hljs.registerLanguage('typescript', require('highlight.js/lib/languages/typescript'));

window.$ = window.jQuery = $;
window.Popper = require('popper.js');
require('bootstrap');
window.hljs = hljs;

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

  // Syntax highlight
  hljs.configure({
    tabReplace: '  ',
  });
  $('.syntax-highlight').each(function() {
    hljs.highlightBlock(this);
  });

  // Settings page: default branch select
  $('#default-branch-select').on('click', '.dropdown-menu a', function() {
    const branchName = $(this).text();
    const dropdown = $('#default-branch-select');
    dropdown.find('input').val(branchName);
    dropdown.find('button').text(branchName);
  });
});
