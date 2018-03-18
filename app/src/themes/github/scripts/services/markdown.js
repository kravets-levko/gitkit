import marked from 'marked';
import highlight from './syntax-highlight';

const renderer = new marked.Renderer();

renderer.code = function(code, lang, escaped) {
  const hl = highlight(code, lang);
  return `<div class="${hl.classes.join(' ')}">${hl.value}</div>`;
};

const options = {
  renderer: renderer,
  gfm: true,
  tables: true,
  breaks: false,
  pedantic: false,
  sanitize: false,
  smartLists: true,
  smartypants: false,
};

export default function(text) {
  return marked(text, options);
};
