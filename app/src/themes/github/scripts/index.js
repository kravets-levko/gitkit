import Vue from 'vue';
import * as components from './components';
import * as directives from './directives';
import './bootstrap';
import { each } from 'lodash';

each(document.querySelectorAll('[vue-widget]'), (element) => {
  new Vue({
    el: element,
    components,
    directives,
  });
});
