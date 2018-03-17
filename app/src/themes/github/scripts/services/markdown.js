import marked from 'marked';

const renderer = new marked.Renderer();
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
