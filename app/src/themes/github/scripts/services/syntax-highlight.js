const hljs = require('highlight.js/lib/highlight');

hljs.configure({
  tabReplace: '  ',
});

hljs.registerLanguage('diff', require('highlight.js/lib/languages/diff'));
hljs.registerLanguage('cpp', require('highlight.js/lib/languages/cpp'));
hljs.registerLanguage('xml', require('highlight.js/lib/languages/xml'));
hljs.registerLanguage('bash', require('highlight.js/lib/languages/bash'));
hljs.registerLanguage('coffeescript', require('highlight.js/lib/languages/coffeescript'));
hljs.registerLanguage('css', require('highlight.js/lib/languages/css'));
hljs.registerLanguage('delphi', require('highlight.js/lib/languages/delphi'));
hljs.registerLanguage('dockerfile', require('highlight.js/lib/languages/dockerfile'));
hljs.registerLanguage('ruby', require('highlight.js/lib/languages/ruby'));
hljs.registerLanguage('go', require('highlight.js/lib/languages/go'));
hljs.registerLanguage('ini', require('highlight.js/lib/languages/ini'));
hljs.registerLanguage('java', require('highlight.js/lib/languages/java'));
hljs.registerLanguage('javascript', require('highlight.js/lib/languages/javascript'));
hljs.registerLanguage('json', require('highlight.js/lib/languages/json'));
hljs.registerLanguage('less', require('highlight.js/lib/languages/less'));
hljs.registerLanguage('lua', require('highlight.js/lib/languages/lua'));
hljs.registerLanguage('makefile', require('highlight.js/lib/languages/makefile'));
hljs.registerLanguage('perl', require('highlight.js/lib/languages/perl'));
hljs.registerLanguage('php', require('highlight.js/lib/languages/php'));
hljs.registerLanguage('python', require('highlight.js/lib/languages/python'));
hljs.registerLanguage('scss', require('highlight.js/lib/languages/scss'));
hljs.registerLanguage('sql', require('highlight.js/lib/languages/sql'));
hljs.registerLanguage('yaml', require('highlight.js/lib/languages/yaml'));
hljs.registerLanguage('twig', require('highlight.js/lib/languages/twig'));
hljs.registerLanguage('typescript', require('highlight.js/lib/languages/typescript'));

function isString(value) {
  return (typeof value === 'string') ||
    (Object.prototype.toString.call(value) === '[object String]');
}

function isNonEmptyString(value) {
  return isString(value) && (value !== '');
}

export default function(text, language, detectLanguage) {
  let result;
  if (isString(language)) {
    result = hljs.highlightAuto(text, [language]);
    if (!isNonEmptyString(result.language) && detectLanguage) {
      result = hljs.highlightAuto(text);
    }
  } else {
    if (detectLanguage) {
      result = hljs.highlightAuto(text);
    } else {
      result = hljs.highlightAuto(text, []); // empty list - no highlight
    }
  }
  result.classes = isNonEmptyString(result.language) ? ['hljs', result.language] : ['hljs'];
  result.value = hljs.fixMarkup(result.value);
  return result;
}
