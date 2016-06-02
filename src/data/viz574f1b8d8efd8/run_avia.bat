#!/bin/bash
PBS_O_WORKDIR=/code/src/data/viz574f1b8d8efd8
cd ${PBS_O_WORKDIR}
/usr/bin/perl /code/annovar/annovar_qsub_wrprIP.pl  -i viz574f1b8d8efd8.txt -f searchTheseDBs.txt -d /code/annovar/humandb -g
/code/src/data/viz574f1b8d8efd8/runAVIA_post.bat
