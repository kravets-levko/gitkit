<template>
  <div class="dropdown position-relative" v-on:focus="delegateFocus()" tabindex="-1">
    <input ref="input" v-bind="$attrs" v-bind:class="parentClass"
      v-bind:data-toggle="items.length > 0 ? 'dropdown' : null" v-model="value">

    <div ng-if="items.length > 0" class="dropdown-menu w-100">
      <a class="dropdown-item" href="javascript:void(0)"
        v-for="item in items" v-html="item.content" v-on:click="setValue(item.value)"></a>
    </div>
  </div>
</template>
<script>
  import { isString, extend, map, filter } from 'lodash';

  export default {
    props: ['list'],
    data() {
      return {
        parentClass: '',
        value: isString(this.$attrs.value) ? this.$attrs.value : '',
      };
    },
    created() {
      // this may not work in future versions of Vue
      this.$data.parentClass = this.$vnode.data.staticClass;
      this.$vnode.data.staticClass = '';
    },
    computed: {
      items() {
        let result = [];
        if (isString(this.$props.list) && (this.$props.list !== '')) {
          const list = document.getElementById(this.$props.list);
          if (list) {
            result = map(list.options, option => ({
              content: option.innerHTML,
              value: option.value,
            }));
          }
        }
        const prefix = this.$data.value.toLowerCase();
        return filter(result, item => item.value.toLowerCase().indexOf(prefix) >= 0);
      },
    },
    methods: {
      delegateFocus() {
        this.$refs.input.focus();
      },
      setValue(value) {
        this.$data.value = value;
      },
    },
  }
</script>