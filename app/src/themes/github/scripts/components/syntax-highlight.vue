<template>
  <div class="source-view" v-bind:class="[classes, componentStateClasses]">
    <div class="source-line" v-for="(line, index) in lines"
      v-bind:class="{highlighted: isHighlighted(index + 1)}">
      <div class="source-line-number" v-if="gutter"
        v-on:click="onSelectRow(index + 1, $event.shiftKey)">{{ index + 1 }}</div>
      <div class="source-line-text" v-html="line"></div>
    </div>
  </div>
</template>
<script>
  import highlight from '../services/syntax-highlight';

  export default {
    props: ['language', 'detect', 'selection', 'gutter'],
    data() {
      return {
        highlightedLines: {first: null, last: null},
      };
    },
    computed: {
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
      compiled() {
        return highlight(
          // TODO: Detect slot changes
          this.$slots.default.map(vnode => vnode.text).join('\n'),
          this.$props.language,
          this.$props.detect
        );
      },
      lines() {
        // Split lines and remove last line if empty
        const lines = this.compiled.value.split('\n');
        if ((lines.length > 0) && (lines[lines.length - 1] === '')) {
          lines.splice(lines.length - 1, 1); // remove last
        }
        return lines;
      },
      classes() {
        return this.compiled.classes;
      },
    },
    methods: {
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
      }
    },
    created() {
      window.addEventListener('hashchange', this.onHashChange, false);
      this.onHashChange();
    },
    beforeDestroy() {
      window.removeEventListener('hashchange', this.onHashChange, false);
    },
  };
</script>
