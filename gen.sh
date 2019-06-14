#!/bin/bash

php -dmemory_limit=1G mkimages.php
for i in `ls *.txt`
do
  echo $i
  php -dmemory_limit=1G show.php ${i%.txt}
done
