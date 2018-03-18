<template>
  <div class="markdown" v-html="compiled"></div>
</template>
<script>
  import markdown from '../services/markdown';

  export default {
    computed: {
      compiled() {
        return markdown(
          // TODO: Detect slot changes
          this.$slots.default.map(vnode => vnode.text).join('\n')
        );
      },
    },
  };
</script>
