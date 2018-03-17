<script>

  import Vue from 'vue';
  import markdown from '../services/markdown';

  export default {
    data() {
      return {
        formatted: '',
      };
    },
    render(h) {
      if (this.$el) {
        // Once DOM node becomes available, process its inner HTML, compile result
        // and replace `render` function of component instance
        this.$data.formatted = markdown(this.$el.textContent);

        const template = Vue.compile('<div v-html="formatted"></div>');
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
