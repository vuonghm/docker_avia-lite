#PBS -S /bin/bash
PBS_O_WORKDIR=/code/src/data/viz574f1b8d8efd8
cd ${PBS_O_WORKDIR}
umask 0000
inputfile=`ls viz574f1b8d8efd8*_wrpr.output | tail -n1`
perl /code/src/data/scripts.dir/genConsequenceCol.pl -f ${inputfile} -g -l viz574f1b8d8efd8.annot.txt -o viz574f1b8d8efd8.annot.txt -k
if [ -e /code/src/data/data/viz574f1b8d8efd8/viz574f1b8d8efd8.annot.txt.html ]; then
	perl /code/src/data/scripts.dir/sendmsg.pl 'vuonghm@mail.nih.gov' 'Thank you for using the AVIA-lite software' '<h2>Your analysis <font color="red">viz574f1b8d8efd8</font> is now complete. </h2> You can directly link to your page by clicking below or by cutting the link below and pasting into any web browser: <br /><a href="fr-s-bsg-avia-t:8000/results.php?id=viz574f1b8d8efd8">fr-s-bsg-avia-t:8000/results.php?id=viz574f1b8d8efd8</a> <br /><br />You can also retrieve other submissions by using our data retrieval page at : <br /><a href="fr-s-bsg-avia-t:8000/retrieve_a_request.php">fr-s-bsg-avia-t:8000/retrieve_a_request.php</a> to retrieve your results by providing your id above.  Your results will be stored for 1 week from the date of submission.'
else
 	perl /code/src/data/scripts.dir/sendmsg.pl 'viz574f1b8d8efd8'
fi
