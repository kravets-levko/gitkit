import $ from 'jquery';
import { map, each } from 'lodash';
import Widget from './widget';
import highlight from '../services/syntax-highlight';
import {
  prepareDiff,
  stringToLines,
  applyDiff,
  mergeLines,
  collapseLines,
  removeTrailingNewline,
} from '../services/diff';

export default class SyntaxHighlight extends Widget {
  static selector() {
    return 'syntax-highlight';
  }

  prepareData() {
    const content = this.$slots.default
      ? this.$slots.default.map(node => node.text()).join('\n')
      : '';

    let diff = [];
    if (this.$slots.diff) {
      try {
        diff = JSON.parse(this.$slots.diff.map(node => node.text()).join('\n'));
      } catch (e) {
      }
    }

    const compiledCurrent = highlight(
      content,
      this.$node.attr('language'),
      this.$node.attr('detect')
    );

    const classes = compiledCurrent.classes;
    let lines = [];
    let hasTrailingNewline = [];
    let hasDiff = false;

    // Process diff
    if (diff.length) {
      hasDiff = true;
      diff = prepareDiff(diff);

      // highlight using the same language
      const compiledPrev = highlight(applyDiff(content, diff),
        compiledCurrent.language, false);

      lines = mergeLines(
        stringToLines(compiledPrev.value),
        stringToLines(compiledCurrent.value),
        diff,
      );
      hasTrailingNewline = removeTrailingNewline(lines);
      lines = collapseLines(lines, 3);
    } else {
      lines = stringToLines(compiledCurrent.value);
      hasTrailingNewline = removeTrailingNewline(lines);
    }

    return { classes, lines, hasDiff, hasTrailingNewline };
  }

  selectRange(first, last) {
    if (this.isSelectingEnabled) {
      if (this.isSingleSelect) {
        last = first;
      }
      this.highlightedLines = {first, last};
      // update hash
      window.location.hash = 'L' + first + (first !== last ? '-L' + last : '');
      this.updateHighlightedLines();
    }
  }

  onSelectRow(index, selectRange) {
    if (this.isSelectingEnabled) {
      const scrollY = window.scrollY;

      if (!this.isMultiSelect) {
        selectRange = false;
      }

      // compute new range
      let first = index;
      let last = index;
      if (selectRange && (this.highlightedLines.first !== null)) {
        first = Math.min(this.highlightedLines.first, index);
        last = Math.max(this.highlightedLines.first, index);
      }
      this.selectRange(first, last);

      // restore scroll position
      window.scrollTo(window.scrollX, scrollY);
    }
  }

  onHashChange() {
    if (this.isSelectingEnabled) {
      const hash = window.location.hash;
      // possible formats: 'L<digits>' or 'L<digits>-L<digits>'
      // window.location.hash includes '#' at beginning
      const values = /^#L([0-9]+)$/.exec(hash) || /^#L([0-9]+)-L([0-9]+)$/.exec(hash);
      if (values) {
        const first = parseInt(values[1], 10);
        const last = parseInt(values[2] || values[1], 10);
        this.selectRange(Math.min(first, last), Math.max(first, last));
      }
    }
  }

  expandBlock(index) {
    const line = this.$data.lines[index];
    if (line && line.isCollapsedBlock) {
      this.$data.lines.splice(index, 1, ...line.lines);
    }
  }

  updateHighlightedLines() {
    if (this.isSelectingEnabled) {
      this.$node.find('.source-line').each((index, line) => {
        if (
          (index + 1 >= this.highlightedLines.first) &&
          (index + 1 <= this.highlightedLines.last)
        ) {
          $(line).addClass('highlighted');
        } else {
          $(line).removeClass('highlighted');
        }
      });
    }
  }

  renderLine(line, data) {
    const result = $('<div>').addClass('source-line');

    if (line.isAdded) result.addClass('added');
    if (line.isRemoved) result.addClass('removed');
    if (line.isCollapsed) result.addClass('muted');
    if (line.isCollapsedBlock) result.addClass('collapsed');
    if (line.isCollapsedBlock && (line.lines.length > 0)) result.addClass('with-children');

    if (this.gutter && !data.hasDiff) {
      result.append(
        $('<div>').addClass('source-line-number')
          .on('click', ($event) => { this.onSelectRow(line.number, $event.shiftKey) })
          .append($('<span>').text(line.number))
      );
    }

    if (line.isCollapsedBlock) {
      if (line.lines.length > 0) {
        const self = this;
        result.append(
          $('<div>').addClass('source-line-number')
            .on('click', function() {
              $(this).parent().replaceWith(
                map(line.lines, nested => self.renderLine(nested, data))
              );
            })
            .append($('<span>').append(data.iconExpand.clone()))
        );
      } else {
        result.append(
          $('<div>').addClass('source-line-number')
            .append($('<span>').html('&hellip;'))
            .append($('<span>').html('&hellip;'))
        );
      }
    }

    if (this.gutter && data.hasDiff && !line.isCollapsedBlock) {
      result.append(
        $('<div>').addClass('source-line-number')
          .append($('<span>').text(line.numberRemoved))
          .append($('<span>').text(line.numberAdded))
      );
    }

    result.append(
      $('<div>').addClass('source-line-text').html(line.value)
    );

    return result;
  }

  render() {
    const data = this.prepareData();
    data.iconExpand = $(
      this.$slots.expand
      ? this.$slots.expand.map(node => node.html()).join('')
      : ''
    );

    const result = $('<div>');
    each(this.$attributes, (attr) => {
      result.attr(attr.name, attr.value);
    });
    result
      .addClass('source-view')
      .addClass(data.classes.join(' '));
    if (this.isSelectingEnabled) {
      result.addClass('selecting-enabled');
    }

    result.append(
      map(data.lines, line => this.renderLine(line, data))
    );

    return result;
  }

  mount($component) {
    super.mount($component);
    this.updateHighlightedLines();
  }

  init() {
    this.selectionMode = (() => {
      const result = ('' + this.$node.attr('selection')).toLowerCase();
      return ((result === 'line') || (result === 'multiline')) ? result : null;
    })();
    this.isSelectingEnabled = !!this.selectionMode;
    this.isSingleSelect = this.selectionMode === 'line';
    this.isMultiSelect = this.selectionMode === 'multiline';
    this.gutter = !!this.$node.attr('gutter');

    this.highlightedLines = {first: null, last: null};

    window.addEventListener('hashchange', this.onHashChange, false);
    this.onHashChange();

    return super.init();
  }
}
