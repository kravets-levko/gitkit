import Vue from 'vue';
import * as components from './components';
import * as directives from './directives';
import './bootstrap';

export default new Vue({
  el: '#application',
  components,
  directives,
});
