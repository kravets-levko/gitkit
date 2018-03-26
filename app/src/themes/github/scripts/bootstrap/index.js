import $ from 'jquery';
import Popper from 'popper.js';

window.$ = window.jQuery = $;
window.Popper = Popper;
import 'bootstrap';

$(() => {
  const body = $('body');

  // Dropdown: allow to stay visible if click inside and close on click outside
  body.on('hide.bs.dropdown', '.dropdown[data-close-outside]', function(event) {
    const nodes = document.querySelectorAll(':hover');
    const elementUnderCursor = nodes[nodes.length - 1];
    const list = this.querySelector('.dropdown-menu');
    if (list.contains(elementUnderCursor)) {
      event.preventDefault();
      event.returnValue = false;
      return false;
    }
  });

  // Tab-alike behaviour when toggle is not `nav-tabs`
  body.on('click', '[data-toggle="xtab"][data-target]', function() {
    const emitter = $(this);
    const target = $(emitter.attr('data-target'));
    if (target.length > 0) {
      const tabs = target.parents('.tab-content').find('.tab-pane')
        .removeClass('show active');
      target.addClass('show active');
    }
  });
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
