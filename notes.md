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

Generate SSH key fingerprint / validate SSH key (print output to stdout)
```
cat id_rsa.pub | ssh-keygen -l -f -
```

Create new SSH key pair (https://www.ssh.com/ssh/keygen/)
```
ssh-keygen -t <algorythm> -b <bits> -f <output filename> -q -N <passphrase> -C <comment>
```

SSH `authorized_keys` format: https://www.ibm.com/support/knowledgecenter/en/SSLTBW_2.3.0/com.ibm.zos.v2r3.foto100/authkeyf.htm
