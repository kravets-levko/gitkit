<script>

  import Vue from 'vue';
  import highlight from '../services/syntax-highlight';

  export default {
    props: ['language', 'detect'],
    data() {
      return {
        classes: [],
        formatted: '',
      };
    },
    render(h) {
      if (this.$el) {
        // Once DOM node becomes available, process its inner HTML, compile result
        // and replace `render` function of component instance
        const hl = highlight(this.$el.textContent, this.$props.language, this.$props.detect);

        this.$data.classes = hl.language ? ['hljs', hl.language] : [];
        this.$data.formatted = hl.value;

        const template = Vue.compile('<div v-bind:class="classes" v-html="formatted"></div>');
        this.$options.render = template.render;
        this.$options.staticRenderFns = template.staticRenderFns;
        return template.render.call(this, h);
      } else {
        // While DOM node is not available, render slot contents
        return h('div', this.$slots.default);
      }
    },
    mounted() {
      // DOM node is available, force re-render
      this.$forceUpdate();
    }
  };

</script>
