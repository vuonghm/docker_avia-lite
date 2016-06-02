#!/bin/sh
#PBS -j oe -l pvmem=16GB,mem=16GB
#PBS -N AVIA_000_0.bat 
umask 000
echo 'System Resource Info: ' `uname -a`
cd /code/src/data/viz574f1b8d8efd8
perl /code/annovar/annotate_variation_ABCC2IP.pl   --buildver hg19 --dbtype cytoBand --batchsize 100k  --regionanno --keepline --silent viz574f1b8d8efd8.txt_000 /code/annovar/humandb 
