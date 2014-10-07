#!/bin/bash

# Set a value that we can use for a datestamp
DATE=`date +%Y-%m-%d_%H`
# Our Base backup directory
BASEBACKUP="/root/backups/mysql"
# Synchronisation IP server
IP="37.187.236.221"

for DATABASE in `cat /root/db-list.txt`; do
echo "Start ${DATABASE}";

# This is where we throw our backups.
FILEDIR="${BASEBACKUP}/${DATABASE}";

# Test to see if our backup directory exists. # If not, create it.
if [ ! -d $FILEDIR ]
then
mkdir -p $FILEDIR
fi
echo "Exporting database: $DATABASE";
mysqldump -uroot -pc8vubd8r $DATABASE | gzip -c -9 > $FILEDIR/$DATABASE-$DATE.sql.gz;
echo -n " ......[ Done ] ";
done;

# AutoPrune our backups. This will find all files
# that are "MaxFileAge" days old and delete them.
MaxFileAge=7
find $BASEBACKUP -name '*.gz' -type f -mtime +$MaxFileAge -exec rm -f {} \;

#Synchronize with fileserver
rsync -rvu --delete ${BASEBACKUP}/* root@${IP}:${BASEBACKUP}/