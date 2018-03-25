<template>
  <div class="source-view" v-bind:class="[classes, componentStateClasses]">
    <div class="source-line" v-for="(line, index) in lines" v-bind:class="{
        highlighted: isHighlighted(index + 1),
        added: line.isAdded,
        removed: line.isRemoved,
        muted: line.isCollapsed,
        collapsed: line.isCollapsedBlock,
        'with-children': line.isCollapsedBlock && (line.lines.length > 0),
      }">

      <div class="source-line-number" v-if="gutter && !hasDiff"
        v-on:click="onSelectRow(index + 1, $event.shiftKey)"><span>{{ line.number}}</span></div>

      <div class="source-line-number" v-if="line.isCollapsedBlock"
        v-on:click="expandBlock(index)"><span v-if="line.lines.length > 0" v-html="iconExpand"
        ></span><span v-if="line.lines.length === 0">...</span><span v-if="line.lines.length === 0">...</span></div>

      <div class="source-line-number" v-if="gutter && hasDiff && !line.isCollapsedBlock"
      ><span>{{ line.numberRemoved}}</span><span>{{ line.numberAdded }}</span></div>

      <div class="source-line-text" v-html="line.value"></div>
    </div>
  </div>
</template>
<script>
  import { map } from 'lodash';
  import highlight from '../services/syntax-highlight';
  import {
    prepareDiff,
    stringToLines,
    applyDiff,
    mergeLines,
    collapseLines,
    removeTrailingNewline,
  } from '../services/diff';

  export default {
    props: ['language', 'detect', 'selection', 'gutter'],
    data() {
      return {
        classes: [],
        lines: [],
        hasDiff: false,
        hasTrailingNewline: true,
        highlightedLines: {first: null, last: null},
      };
    },
    computed: {
      iconExpand() {
        return this.$slots.expand
          ? this.$slots.expand.map(vnode => vnode.text).join('\n')
          : '';
      },
      selectionMode() {
        const result = ('' + this.$props.selection).toLowerCase();
        return ((result === 'line') || (result === 'multiline')) ? result : null;
      },
      isSelectingEnabled() {
        return !!this.selectionMode;
      },
      isSingleSelect() {
        return this.selectionMode === 'line';
      },
      isMultiSelect() {
        return this.selectionMode === 'multiline';
      },
      componentStateClasses() {
        const result = [];
        if (this.isSelectingEnabled) {
          result.push('selecting-enabled');
        }
        return result;
      },
    },
    methods: {
      prepareLines() {
        const content = this.$slots.default
          ? this.$slots.default.map(vnode => vnode.text).join('\n')
          : '';

        let diff = [];
        if (this.$slots.diff) {
          try {
            diff = JSON.parse(this.$slots.diff.map(vnode => vnode.text).join('\n'));
          } catch (e) {
          }
        }

        const compiledCurrent = highlight(
          content,
          this.$props.language,
          this.$props.detect
        );
        this.$data.classes = compiledCurrent.classes;

        // Process diff
        if (diff.length) {
          this.$data.hasDiff = true;
          diff = prepareDiff(diff);

          // highlight using the same language
          const compiledPrev = highlight(applyDiff(content, diff),
            compiledCurrent.language, false);

          const lines = mergeLines(
            stringToLines(compiledPrev.value),
            stringToLines(compiledCurrent.value),
            diff,
          );
          this.$data.hasTrailingNewline = removeTrailingNewline(lines);
          this.$data.lines = collapseLines(lines, 3);
        } else {
          const lines = stringToLines(compiledCurrent.value);
          this.$data.hasTrailingNewline = removeTrailingNewline(lines);
          this.$data.lines = lines;
        }
      },
      selectRange(first, last) {
        if (this.isSelectingEnabled) {
          if (this.isSingleSelect) {
            last = first;
          }
          this.$data.highlightedLines = {first, last};
          // update hash
          window.location.hash = 'L' + first + (first !== last ? '-L' + last : '');
        }
      },
      onSelectRow(index, selectRange) {
        if (this.isSelectingEnabled) {
          const scrollY = window.scrollY;

          if (!this.isMultiSelect) {
            selectRange = false;
          }

          // compute new range
          let first = index;
          let last = index;
          if (selectRange && (this.$data.highlightedLines.first !== null)) {
            first = Math.min(this.$data.highlightedLines.first, index);
            last = Math.max(this.$data.highlightedLines.first, index);
          }
          this.selectRange(first, last);

          // restore scroll position
          window.scrollTo(window.scrollX, scrollY);
        }
      },
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
      },
      isHighlighted(index) {
        return (index >= this.$data.highlightedLines.first) &&
          (index <= this.$data.highlightedLines.last);
      },
      expandBlock(index) {
        const line = this.$data.lines[index];
        if (line && line.isCollapsedBlock) {
          this.$data.lines.splice(index, 1, ...line.lines);
        }
      },
    },
    created() {
      window.addEventListener('hashchange', this.onHashChange, false);
      this.onHashChange();
      this.prepareLines();
    },
    beforeDestroy() {
      window.removeEventListener('hashchange', this.onHashChange, false);
    },
  };
</script>
