Count lines (read from stdin)
```
$ cat <file> | wc --lines
21
```

Count non-empty lines (LOC) (read from stdin)
```
$ cat <file> | grep --count --invert-match --line-regexp '^\s*$'
17
```

Get file media type (MIME) (read from stdin)
```
$ cat <file> | file --brief --mime-type -
text/plain
```
