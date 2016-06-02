#!/bin/sh
#PBS -j oe -l pvmem=8GB,mem=8GB
#PBS -N AVIA_Annot_000.bat 
umask 000
echo 'System Resource Info: ' `uname -a`
cd /code/src/data/viz574f1b8d8efd8
perl /code/annovar/annotate_variation_ABCC2IP.pl  --buildver hg19  --extype --geneanno  --keepline --relpos  viz574f1b8d8efd8.txt_000 /code/annovar/humandb 
