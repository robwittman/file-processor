files=$(cat register.txt)
rm -f html/index.php
echo "<?php\n" >> html/index.php
for LINE in ${files}; do
    cat "./src/${LINE}" | sed '/namespace/d' | sed '/<?php/d' | sed '/use FileProcessor/d' >> html/index.php;
done;
echo "?>\n" >> html/index.php
cat html/_index.php >> html/index.php
