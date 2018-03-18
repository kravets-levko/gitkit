<template>
  <div class="position-relative" v-fix-class="['form-control', 'is-valid', 'is-invalid']"
    tabindex="-1" v-on:focus="onFocus()">
    <input v-bind:data-toggle="items.length > 0 ? 'dropdown' : null"
      v-bind="$attrs" v-on="$listeners" v-model="value" v-bind:class="classNames" ref="input">
    <div v-if="items.length > 0" class="dropdown-menu w-100">
      <a v-for="item in items" v-html="item.text" v-on:click="setValue(item.value)"
        class="dropdown-item" href="javascript:void(0)"></a>
    </div>
  </div>
</template>
<script>
  export default {
    props: ['list', 'value'],
    inheritAttrs: false,
    data() {
      return {
        classNames: [],
      };
    },
    computed: {
      items() {
        const options = document.querySelectorAll('#' + this.$props.list + ' option');
        const result = [];
        for (let i = 0; i < options.length; i++) {
          result.push({
            text: options[i].innerHTML,
            value: options[i].value,
          });
        }
        return result;
      },
    },
    methods: {
      onFocus() {
        this.$refs.input.focus();
      },
      setValue(value) {
        this.$refs.input.value = value;
        this.$emit('input', value);
      },
      fixClassNames(classNames) {
        this.$data.classNames = classNames;
      },
    },
    directives: {
      fixClass: {
        bind(element, bindings, vnode) {
          const existing = [];
          bindings.value.forEach((className) => {
            if (element.classList.contains(className)) {
              existing.push(className);
            }
            element.classList.remove(className);
          });
          vnode.context.fixClassNames(existing);
        },
        update(element) {
          element.classList.remove('form-control');
        },
      },
    },
  }
</script>