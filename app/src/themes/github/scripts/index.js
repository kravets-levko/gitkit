import { each } from 'lodash';
import * as components from './components';
import './bootstrap';

each(components, component => component.bootstrap());

