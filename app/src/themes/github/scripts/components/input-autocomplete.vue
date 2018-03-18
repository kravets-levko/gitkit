<template>
  <div>
    <slot name="input"></slot>
    <slot name="dropdown" v-bind:set-value="setValue"></slot>
  </div>
</template>
<script>
  export default {
    methods: {
      setValue(value) {
        if (this.$el) {
          const input = this.$el.querySelector('input');
          if (input) {
            input.value = value;
          }
        }
      },
    },
  }
</script>