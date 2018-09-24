### cmd:

~~~~~sh
$ cd .../codem/manage/odd/
$ ( fiumicino.bash codem.odd && cd ../samplexml/jqa/ && for f in *.xml ; do echo "" ; echo "---------$f:" ; jing ../../odd/codem.rng $f | perl -pe 's,^/Users/syd/Documents/ridgeback/codem/manage/samplexml/jqa/,,;s,;.*$,,;' ; probatron -t ../../odd/codem.isosch $f ; done 2>&1 > validation.txt )
~~~~~

OR

~~~~~sh
$ cd .../codem/samplexml/jqa/
$ ( cd ../../odd/ && fiumicino.bash codem.odd && cd - && for f in *.xml ; do echo "" ; echo "---------$f:" ; jing ../../odd/codem.rng $f | perl -pe 's,^/Users/syd/Documents/ridgeback/codem/manage/samplexml/jqa/,,;s,;.*$,,;' ; probatron -t ../../odd/codem.isosch $f ; done 2>&1 > validation.txt )
~~~~~


Issues
------
WELL-FORMEDNESS! (Sorry for yelling.)

There are 2 "jqadiaries-v49-1826-06-26" IDs

What should new namespace needed for CODEM components be?

Are @codem:startingOnPage values alwyas positive integers?

Are @codem:volume values always positive integers?
