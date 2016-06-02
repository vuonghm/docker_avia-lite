#!/usr/bin/perl 
=head v2.0
This version is uses the fact the website accepts gzip, bzip, tar, tgz file formats for upload
So we must sanitize the inputs before moving to our work directory  (for ia_upload_only)
No longer accepts lmt inputs for checks!

v3.0
addition of viz options in the mix
=cut
use strict;
use FindBin;
use lib "$FindBin::Bin";
use lib "$FindBin::Bin/perl_modules";
use lib "$FindBin::Bin/perl_modules/Config-IniFiles-2.38";
# use lib "/bioinfoC/AVA/prod_scripts/util/perl_modules";
use Mail::Sendmail;
use Config::IniFiles;
use Getopt::Std;
use parse;
use Data::Dumper;
umask(0000);
use vars qw( $opt_o $opt_f $opt_c $opt_t $opt_w);
getopts("f:c:t:o:w");
########### FIND EXECUTABLES FOR RUNNING PIPELINE (make sure they exist on WEB SERVER!!!) ############
my $tar=`which tar`;chomp $tar;
my $gunzip=`which gunzip`;chomp $gunzip;
my $bzip=`which bunzip2`;chomp $bzip;
my $unzip=`which unzip`;chomp $unzip;
my $ADMIN="vuonghm\@mail.nih.gov";## put your email address here
my $perl=`which perl`;chomp $perl  ; 
#my $qsub=`which qsub`;chomp $qsub;
my $_rename=0;	my @files;
my $refver;#this can be changed during the course of this script where as $cfg{'USER'}{'refver'} is NOT, preserves original request info
my $bin=`dirname $0`;chomp $bin;
my $dbinfofile='/code/annovar/humandb/database.info';
my $liftover_exe='/opt/nasapps/production/liftover/2013-05-01/bin/liftOver';
my $liftover_files='/SeqIdx/liftOver/';
umask(0000);


### Application specific user id ### Change this to your server's web user account
my $webaddon="";
my $application_name='AVIA-lite';

### if you are using PBS, modify your submission executable here
#if (!$qsub && ! -e "/usr/local/torque/bin/qsub") {
#	 die "could not run this executable at LN $0 ".__LINE__." ($qsub) and /usr/local/torque/bin/qsub does not exist on server \n";
#}elsif (!$qsub){
#	$qsub="/usr/bin/qsub";
#/}
my $usage=qq(
	$0 -f <web_input> -o <config_output>
);
#SAFE_PROCESS ("source /users/abcc/vuonghm/.cshrc.common",__LINE__);
my $out_fn;my %input_elements; 
die "$usage\n" if (!defined $opt_f && !defined $opt_c);
if (defined $opt_o){
	$out_fn=$opt_o;
}else{
	$out_fn="$opt_f.config.ini";
}
# print "writing to $out_fn...\n";
my $date=`date "+%Y%m%d"`;chomp $date;
$webaddon='' if (!$opt_w);
#################Variable Declarations and zip formats ####################
my $BASE_DIR;
my $web_bin=`dirname $0`;chomp $web_bin;
my $annovar_dir='/code/annovar/';#changes to convert
################Read in user inputs########################
my ($isFatal_msg,$err_msg);
my ($web_input,$email,%cfg);
my $web_dir=`dirname $opt_f`;chomp $web_dir;
if (defined $opt_c){
	tie %cfg , 'Config::IniFiles', (-file =>"$opt_c");
	$email=$cfg{'UserInfo'}{'email'};
	$web_input=$opt_f;
	system ("ln $opt_c $out_fn\n");
}elsif (defined $opt_f){
	$web_input=$opt_f;
	##This is where we append user config files
	my $user_config=`grep ^user.email= $web_input`;chomp $user_config;
	$email=($user_config=~/email=(\S+)\@/)?$1:'';
	sendMsg ("ERROR","no email could be found $user_config\n",'') if (!$email);
	SAFE_PROCESS("$perl $web_bin/convertTxtToConfig.pl -f $web_input -o $out_fn",__LINE__);#generates $out_fn
	tie %cfg , 'Config::IniFiles', (-file =>"$out_fn");
}
################read in config ######################
my $scripts_bin=$cfg{'Utilities'}{'scripts_dir'};chomp $scripts_bin;
my $original_dir=`dirname $0`;chomp $original_dir;chop if ($original_dir=~/\/$/);
$original_dir=`dirname $original_dir`;chomp $original_dir;
my $label=$cfg{'Target_Info'}{'label'};
my $workdir=$original_dir."/$label";
#print "[INFO] workdir $workdir and original_dir=$original_dir\n";
my $webUrl=$cfg{'UserInfo'}{'HOST'};
SAFE_PROCESS("mkdir $original_dir/$label",__LINE__)  if (! -e "$original_dir/$label");
SAFE_PROCESS("cp $opt_f $original_dir/$label/",__LINE__);
my $af=0;#calculate allele frequency from vcf file for jason lih
################## Move to AVA work directory #############################
open (LOG,">$workdir/web_wrapper.log") or sendMsg("ERROR, write err", "cannot open $workdir/webwrapper.log for writing\n",'');
####################### REINSTATE ########################
open (STDERR, ">>&LOG"); open (STDOUT,">>&LOG");
print STDERR "Redirecting stderr and stdout to $workdir/web_wrapper.log\n";
##########################################################
if ((-e "$workdir/$out_fn") && (`diff $out_fn $workdir/$out_fn | wc -l` >1)){#session id will always be different
	my $ct=1;my $curr="$workdir/$out_fn";
	until (! -e "$workdir/$out_fn\.$ct") {
		if (`diff $out_fn $workdir/$out_fn.$ct | wc -l `>0){
			$ct++;
		}else{
			last;
		}
	}
	SAFE_PROCESS("cp $curr $workdir/$out_fn\.$ct\n",$0,__LINE__);
}
#SAFE_PROCESS ("cp $out_fn $workdir/config.ini\n",__LINE__);
################## Main  #############################
chdir ("$workdir");
my $program=$cfg{'UserInfo'}{'program'};
my $runcmd=printCmdFile();
print "About to run $runcmd \n";
my $gid=SAFE_PROCESS("cd $workdir;$runcmd",__LINE__);
if (!$gid){
	sendMsg( "ERROR, QSUB submission", "could not submit the $runcmd to the grid for processing\nPlease check director $workdir at $0 LN".__LINE__."\n","");
	exit;
}
my $isDone="";		
my $convert=`which convert`;chomp $convert;
my $date=`date "+%Y%m%d"`;chomp $date;

sub printCmdFile{
	#This generates all files in order neeeded by AVIA, moves everything to where it needs to be 
	# generates bat files for running the pipeline
	my $cmd;$refver='';
	my $_user_original;
	my ($filelocation,$filename);
	my $noalleles=0;
	if (exists $cfg{'UserInfo'}{'file'}){
		#this is for uploaded files; need to move off of avia webserver as SGE cannot access these directories
		$filelocation=`dirname $cfg{'UserInfo'}{'file'}`;
		$filename=`basename $cfg{'UserInfo'}{'file'}`;
	}elsif (exists $cfg{'UserInfo'}{'input_path'}){
		die "Haven't coded yet\n";
	}else{
		sendMsg("ERROR", "Could not find file or input_fullpath in your config file\n");
	}
	chomp $filelocation;
	chomp $filename;
	$_user_original=$filename;$_user_original=~s/^(\d{14}\.)//;
	#check that the original file is not zipped or tarred
	if ($filename=~/(zip|tar|gz|bz2*)$/){
		#untar change filename and change the config file, but do not rename the file
		$_rename=0;
		print "About to check And Sanitize ($cfg{'UserInfo'}{'file'})\n";
		$filename=pop(@{checkAndSanitize($cfg{'UserInfo'}{'file'})});
	}
	#this is on the server and do not need to do anything more
	if (exists $cfg{'UserInfo'}{'wastyped'} && $cfg{'UserInfo'}{'wastyped'}){
		if (!-e ("$filelocation/$filename")){
			print "[ERROR] $filelocation/$filename does not exist...exiting\n";exit;
		}
		if (`grep -P '\t' $filelocation/$filename | wc -l`==0){
			# system ("perl -pe '\$_=join \"\t\", split /\\s+/, \$_, 6'  $filelocation/$filename > $workdir/$filename\n");
			open (FILE,"<$filelocation/$filename" ) or die "Cannot open $filelocation/$filename\n";
			open (OUT,">$workdir/ANNOVAR.input" ) or die "Cannot open $workdir/ANNOVAR.input\n";
			print "[INFO] WRiting to $workdir/ANNOVAR.input<br />\n";
			while (my $line=<FILE>){
				my $cnt = 0;
				$line=~s/\s{2,}/ /g;
				
				# $line=~s/\s{1,}/\t/g;
				print OUT "$line\n";
			}
			close OUT;close FILE;
		}else{
			SAFE_PROCESS ("/usr/bin/dos2unix $filelocation/$filename\n",__LINE__) if (-e "/usr/bin/dos2unix");
			SAFE_PROCESS( "cp $filelocation/$filename $workdir/ANNOVAR.input \n",__LINE__);
			print "[INFO] Copied $filelocation/$filename to $workdir/ANNOVAR.input on line". __LINE__."\n<br />";
		}
	}else{
		if ($filename=~/^\//){
			my $base=`basename $filename`;chomp $base;
			SAFE_PROCESS( "cp $filename $workdir/$base \n",__LINE__);
			$filename=$base;
		}else{
			SAFE_PROCESS( "cp $filelocation/$filename $workdir/ANNOVAR.input \n",__LINE__);
			print "[INFO] Copied $filelocation/$filename to $workdir/ANNOVAR.input\n<br />";
		}
	}
	my $db_loc="/code/annovar/";
	my $type=$cfg{'UserInfo'}{'inputformat'};my $annotated=0;
	my $convert_exe="$perl $annovar_dir/convert2annovar.pl -format ";
	if ($type=~/(pileup|cg|gff3-solid)/i){
		#need to convert to annovar format;
		SAFE_PROCESS ("$convert_exe $1  -outfile $label.txt ANNOVAR.input \n",__LINE__);
		$filename="$label.txt";
	}elsif ($type=~/(soap|maq|vcf4|casava|clcbio|modhgvs|bed|hgvs|notgenomic)/i){
		my $t=$1;
		if ($type eq 'notgenomic'){
			 $convert_exe="$perl $annovar_dir/convert2annovar.pl --includeinfo -format $type --outfile $label.txt ANNOVAR.input";
			$noalleles=1;
			SAFE_PROCESS($convert_exe,__LINE__);
			make_comments('6');
		}else{
			my $addon='';
			if (exists $cfg{'UserInfo'}{'splitZyg'} && $cfg{'UserInfo'}{'splitZyg'}==1 && $type=~/vcf/i){
				$addon=' --withzyg ';
			}
			SAFE_PROCESS ("$convert_exe $t $addon --includeinfo --outfile $label.txt ANNOVAR.input\n",__LINE__);
			my $cols;
			if ($type=~/bed/){
				$cols='11-100';
			}elsif ($type=~/vcf/){
				$cols='13';
			}else{
				$cols='10-100';
			}
			make_comments($cols);
		}
		
		$filename="$label.txt";
	}elsif ($type=~/(vcf|annovar)/){
		#annot_vcf,vcf,annot_anvr,anvr
		if ($type=~/^vcf$/){
			my $addon= "-allallele ";
			if (exists $cfg{'UserInfo'}{'calcAF'} && $cfg{'UserInfo'}{'calcAF'}=~/true/i){
				$addon.=" -af ";
				$af=1;
			}else{
				$addon='';
			}
			if (exists $cfg{'UserInfo'}{'splitZyg'} && $cfg{'UserInfo'}{'splitZyg'}==1 && $type=~/vcf/i){
				$addon.=' -withzyg ';
			}
			SAFE_PROCESS ("$convert_exe vcf4  --includeinfo $addon --outfile $label.txt ANNOVAR.input\n",__LINE__);
			#check the output file for af
			if ($af && -e "cvrt2anvr.stderr.log"){
				my $pass=` grep -e no_af -e afct cvrt2anvr.stderr.log | wc -l`;
				$af=1 if ($pass>0);
			}
			$filename="$label.txt";
		}
		if ($type=~/annot/){
			$annotated=1;
		}
		make_comments('11-100');
	}else{
		system ("ln -s $filename $label.txt\n");
		$filename="$label.txt";
	}

	#make a file with the database to run against
	my $copyOver='';
	if ($cfg{'UserInfo'}{'ver'}=~/mm/){
		$db_loc.="mousedb/";$dbinfofile=~s/XXXX/mousedb/;
		$refver=$cfg{'UserInfo'}{'ver'};
	}else{
		$db_loc.="humandb/";$dbinfofile=~s/XXXX/humandb/;
		
		if ($cfg{'UserInfo'}{'ver'}=~/hg18/ && $cfg{'UserInfo'}{'webuser'}==0){
			#convert here
			open (OLD,"<$filename") or die "Cannot open file $filename for rewriting for conversion\n";
			open (CONVERTED,">$filename.toconvert") or die "Cannot open file $filename for rewriting for conversion\n";
			while (<OLD>){
				next if ($_=~/^#/);
				my @arr=split("\t",$_);chomp $arr[$#arr];
				if ($arr[0]!~/chr/){
					$arr[0]='chr'.$arr[0];
				}
				print CONVERTED "$arr[0]:$arr[1]-$arr[2]\t$arr[3]\t$arr[4]\n";
			}
			close CONVERTED;close OLD;
			$filename.=".toconvert";
			print STDERR "[INFO] Running $liftover_exe $filename $liftover_files/hg18ToHg19.over.chain  hg19_converted.tmp hg19_failed -positions...\n";
			SAFE_PROCESS ("$liftover_exe $filename $liftover_files/hg18ToHg19.over.chain hg19_converted.tmp hg19_failed -positions\n",__LINE__);
			#now convert back to ANNOVAR format
			print STDERR "[INFO] Running /bioinfoC/hue/annovar/merge_liftover_files.pl $filename hg19_converted.tmp hg19_failed $label.txt $label\n";
			SAFE_PROCESS ("$perl /bioinfoC/hue/annovar/merge_liftover_files.pl $filename hg19_converted.tmp hg19_failed hg19_$label.txt $label\n");
			my $newfilename='hg19_$label.txt';chomp $newfilename;
			if (-e $newfilename && -z $newfilename){
				sendMsg("ERROR converting hg18 to hg19","$filename was improperly formatted or has no valid conversions\n",'');
				exit;
			}
			$filename='hg19_$label.txt';
			$copyOver.="hg19_hg18converted.txt";
			$refver='hg19';
		}else{
			$refver='hg19';
		}
	}
	###################################### 11/04/13 changed ######################################
	##now write the  post processing scripts to bat file
	open (DBFILE,">$workdir/searchTheseDBs.txt" ) ;#$workdir/
	print "writing to $workdir/searchTheseDBs.txt...";
	if ($cfg{'UserInfo'}{'ver'} ne "$refver"){
		print DBFILE "$workdir/hg19_hg18converted.txt\n";
	}
	my @filterdb;
	DBFILE: foreach my $dbfile (keys %{$cfg{'UserInfo'}}){
		# hv added for filter computations:
		if ($dbfile=~/filter(\d+)/){
			push(@filterdb,"$cfg{'UserInfo'}{$dbfile}\t". $cfg{'UserInfo'}{"keep$1"}."\n");next;
		}
		next if ($dbfile !~/annotdb_(.*)/ && $dbfile!~/^userdefined_annotdb/);#db_snp132=on
		my $db=$1;
		if ($db ne '' ){#these are the standard databases available in annovar; they are in the /SeqIdx/annovardb/<org>db/ directories
			if ($db!~/userdefined/i){
				if ($db=~/(PTM|Flanking)/){
					push(@files, "$db_loc/$refver\_$db")
				}else{
					push(@files, "$db_loc/$refver\_$db.txt") ;#let annovar_wrpr take care of the dbs that do not exist for the specified organism
				}
			}else{
				next;
			}
		}else{#these are the user uploaded files #userdefined_db1=myfile.txt
			my $file=`basename $cfg{'UserInfo'}{$dbfile}`;chomp $file;
			$file="hg19_".$file;
			my $db_count=($dbfile=~/annot(db\d+)/)?"userdefined_$1":$file;
			#move to the work directory that is accessible on the grid and so we can uncompress if necessary
			if (exists $cfg{'UserInfo'}{$db_count}){
				system ("cp $db_count} $workdir/$file\n",__LINE__);#this is the zip/tar files, better to move around compressed files
			}elsif(-e "$original_dir/upload/$cfg{'UserInfo'}{$dbfile}"){
				SAFE_PROCESS ("cp $original_dir/upload/$cfg{'UserInfo'}{$dbfile} $workdir/$file\n",__LINE__);
			}else{
				print "whaat?? $cfg{'UserInfo'}{$db_count} does not exist and -e $original_dir/upload/$cfg{'UserInfo'}{$dbfile} does not exist on server!\n";
				next;
			}

			#if the file is not a zip/tar format
			if ($file!~/(zip|tar|tgz|gz)$/){
				if (! -e "$file" || `file $file`!~/ASCII.*text/i){
					#do not process anything that isn't a text file
					next;
				}
				$file=abcc_rename($file);
				push(@files, "$workdir/$file");
			}else{
				#move the file to the final directory
				my $arr=checkAndSanitize($file);#an array of filenames is returned in case there are more than one files
				print "FOOOO..." .join "\n",@{$arr};exit;
				if ($#{$arr}==-1){#no acceptable files could be extracted
					sendMsg ("ERROR:checkAndSanitize", "Could not execute checkAndSanitize on $file\n$?");die;
				}else{
					foreach my $em (@{$arr}){
						push(@files, "/code/src/data/$label/$em");#see next note for why we need to include the \n here
					}
					###do not change the next line...logic foreach of the tar'd files, we add it to the list of databases to check.  
					###But these should not have any scores because there is no way to get the user score filter to this 
					###line at this time, score is requred to have the format: \d+,\d+ and not just one column for annovar
					next DBFILE;
				}
			}
		}
		if (($cfg{'UserInfo'}{'filter'}=~/yes/i) && $dbfile=~/(sift|polyphen)/i ){
			$files[$#files].="\t".$cfg{'UserInfo'}{"$1\_cutoff"};
		}
	}
	reorder(\*DBFILE);
	my $filter_addon='';

	if ($cfg{'UserInfo'}{'modulesid'}==6){
		#do some filtering first;
		open (FILORDER,">$workdir/filter_order.txt") or die "Cannot open filter_order.txt";
		foreach my $ids (sort keys %{$cfg{'UserInfo'}}){
			if ($ids=~/^filter(\d+)/){
				print FILORDER "$cfg{'UserInfo'}{$ids}\t". $cfg{'UserInfo'}{"keep$1"};
				if (exists $cfg{'UserInfo'}{"keep$1cutoff"}){
					print FILORDER "\t".$cfg{'UserInfo'}{"keep$1cutoff"}."\n";
				}else{
					print FILORDER "\t0\n";
				}
				
			}
			
		}
		close FILORDER;
		system ("$perl $annovar_dir/annovar_qsub_wrprIP.pl -F filter_order.txt -i $label.txt -g -W -j filtered_results.txt\n"); 
		$filename="filtered_results.txt";
		$copyOver.=" filtered_results.txt ";
	}
	$cmd='';
	# my $runfiltergenelist=getFromCfg(\%cfg);
	my ($cmd,$othercmd);
	close DBFILE;
	my $found=0;

	####################################################################
	# #write post processing script for running 
	# This is where you would add any post processing scripts for the bat file
	# Add it to the variable $cmd.="command\n"
	# Then uncomment out the next lines:
	#####################################################################
	# my $user_orig_fn=$filename;
	# open (BAT, ">$original_dir/data/$label/runAVIA_post.bat" ) || sendMsg ("ERROR, File I/O", "Cannot open runAVIA_post.bat in $workdir\n","");
	# print BAT "#PBS -S /bin/bash\nPBS_O_WORKDIR=$original_dir/data/$label\n". 
	# "#PBS -l cput=290:000:00,pcput=290:00:00,walltime=290:00:00\n".
	# "#PBS -j eo -o $original_dir/data/$label/post_processing.out\n".
	# "cd \${PBS_O_WORKDIR}\numask 0000\n";
	# print BAT "$cmd\nchmod 777 $original_dir/data/$label -R 2>/dev/null\n";#deleted 1st $runAnnovar\n
	# print BAT "\n$perl /mnt/webrepo/fr-s-abcc-avia$doc_root/public/scripts/sendmsg.pl '". $cfg{'UserInfo'}{'email'} ."' 'Thank you for using the AVIAv2.0 software' '<h2>Your analysis <font color=\"red\">$label</font> is now complete. </h2> You can directly link to your page by clicking below or by cutting the link below and pasting into any web browser: <br />".
	# "<a href=\"http://avia$dev.abcc.ncifcrf.gov/apps/site/results/?id=$label\">http://avia$dev.abcc.ncifcrf.gov/apps/site/results/?id=$label</a> <br /><br />You can also retrieve other submissions by using our data retrieval page at : <br />".
	# "<a href=\"http://avia$dev.abcc.ncifcrf.gov/apps/site/retrieve_a_request\">http://avia$dev.abcc.ncifcrf.gov/apps/site/retrieve_a_request</a> to retrieve your results by providing your id above.  Your results will be stored for 1 week from the date of submission.'\n";
	# close BAT;

	#start fdi by writing to bat file and running on grid
	#my $phpcmd="php /code/src/FDI/test.php $label 2>/dev/null\n";
	my $phpcmd="perl /code/src/data/scripts.dir/genConsequenceCol.pl -f $label.annovar_wrpr.output -g -l $label.annot.txt -o $label.annot.txt -k";
	print LOG "<br />[INFO] Writing to $original_dir/$label/runAVIA_post.bat???<br />";
	open (FDI,">$original_dir/$label/runAVIA_post.bat"); 
	print FDI "#PBS -S /bin/bash\nPBS_O_WORKDIR=$original_dir/$label\n". 
#	"#PBS -l cput=290:000:00,pcput=290:00:00,walltime=290:00:00\n".
#	"#PBS -j oe -o $original_dir/$label/fdi_run.log\n".
	"cd \${PBS_O_WORKDIR}\numask 0000\n".
	"inputfile=`ls $label*_wrpr.output | tail -n1`\n".
	"perl /code/src/data/scripts.dir/genConsequenceCol.pl -f \${inputfile} -g -l $label.annot.txt -o $label.annot.txt -k\n".
	#"$perl $annovar_dir". "annovar_qsub_wrprIP.pl  -i $label.txt -f searchTheseDBs.txt -d /code/annovar/humandb -g \n".
	"if [ -e $original_dir/data/$label/$label.annot.txt.html ]; then".
	"\n\tperl /code/src/data/scripts.dir/sendmsg.pl '". $cfg{'UserInfo'}{'email'} ."' 'Thank you for using the $application_name software' '<h2>Your analysis <font color=\"red\">$label</font> is now complete. </h2> You can directly link to your page by clicking below or by cutting the link below and pasting into any web browser: <br />".
	"<a href=\"$webUrl/results.php?id=$label\">$webUrl/results.php?id=$label</a> <br /><br />You can also retrieve other submissions by using our data retrieval page at : <br />".
	"<a href=\"$webUrl/retrieve_a_request.php\">$webUrl/retrieve_a_request.php</a> to retrieve your results by providing your id above.  Your results will be stored for 1 week from the date of submission.'\n" .
	"else\n ".
	"\tperl /code/src/data/scripts.dir/sendmsg.pl '$label'\n". 
	"fi\n";
	close FDI;
	system ("chmod 777 $original_dir/$label/runAVIA_post.bat\n");
	open (BAT, ">$original_dir/$label/run_avia.bat") || die "Cannot open $original_dir/$label/run_avia.bat\n";
	print BAT "#!/bin/bash\nPBS_O_WORKDIR=$original_dir/$label\n".
		"cd \${PBS_O_WORKDIR}\n".
		"$perl $annovar_dir". "annovar_qsub_wrprIP.pl  -i $label.txt -f searchTheseDBs.txt -d /code/annovar/humandb -g\n".
		"$original_dir/$label/runAVIA_post.bat\n";
	close BAT;
	print LOG "About to run $original_dir/$label/run_avia.bat\n";
	system ("chmod +x $original_dir/$label/run_avia.bat\n");
	print LOG "<br />$original_dir/$label/run_avia.bat 2>>$original_dir/$label/run_avia.log\n";
	system("$original_dir/$label/run_avia.bat 2>>$original_dir/$label/run_avia.log &\n");
	exit;
}
sub make_comments{
	my $xcols=shift;
	print LOG "cut -f1-5,$xcols $label.txt |grep -ve '^#' |perl -pe '\$_=join \":\", split /\\t+/, \$_, 5' | sed 's/^chr//' | sed 's/\\t/ | /g' |perl -pe '\$_=join \" | \", split / \| /, \$_, 1'> $label.hg19_comments\n";
	system ("cut -f1-5,$xcols $label.txt |grep -ve '^#' |perl -pe '\$_=join \":\", split /\\t+/, \$_, 5' | sed 's/^chr//' | sed 's/\\t/ | /g' | perl -pe '\$_=join \"\\t\", split / /, \$_, 2'> $label.hg19_comments\n");
	return;
}
sub SAFE_PROCESS {
	my $cmd=shift;
	my $line_nbr=shift;
	chomp $cmd if ($cmd=~/\n$/);
	print LOG "Running $cmd at $line_nbr\n";
	if ($cmd=~/qsub/){
		my $id=`$cmd`;chomp $id;
		if ($id=~/(\d+.abcc1)/){
			return $1;
		}elsif ($id=~/(\d+)/){
			print LOG "Grid submission entry: $id\n";
			return $1;
		}else{
			sendMsg("[AVIA-L]ERROR, QSUB", "execution of command from $0 ($label)at line $line_nbr FAILED\n$?");
			die;
		}
	}else{
		eval {
			#print STDERR "$cmd\n";
			system ("$cmd 2>&1\n");	
		};
		if ($?){
			sendMsg ("[AVIA-L]ERROR, PROCESSING", "execution of $cmd from $0 ($label) at line $line_nbr FAILED\nErrMsg($?)");
			die;
		}else{
			return 1;
		}
	}
}
sub sendMsg{
	#print "REINSTATE messages!\n";
	my $subject=shift;
	my $msg=shift;
	my $email_addy=shift;
	if ($subject=~/error/){
		exit;
	}
	if ($email_addy ne '' ){
		$ADMIN.=",$email_addy";
		
	}
	my  %mail = ( To  =>   "$ADMIN",
			BCC => 'vuonghm@mail.nih.gov',
            From    => 'AVIA_docker@mail.gov',# Put your administrative email here
            Subject=> "$subject",
            Message => "\n$msg\n"
           );

  ##REINSTATE sendmail(%mail) or die $Mail::Sendmail::error;
  if ($subject=~/error/i){
  		print STDERR "$msg\n";
  		exit (1);
  }else{
		return;
  }
}
sub abcc_rename{
	my $prename=shift;
	if (!$_rename){return $prename;}
	my $orig=$prename;
	if ($prename!~/^($cfg{'UserInfo'}{'ver'}|$refver)\_/){
		$prename=$cfg{'UserInfo'}{'ver'}."_$prename";
	}
	if ($prename!~/.txt/){
		$prename.=".txt";
	}
	if ($prename ne "$orig"){
		system ("mv $orig $prename\n");
	}
	print "in abcc_rename.. $prename $orig\n";
	return $prename;
}
sub checkAndSanitize{#NOTE: we use this for both databases and the user input file
	my $xfile=shift;
	my $xcmd='';my @xarr;
	my ($arrOfFiles,$dirOfFiles);
	if ($xfile=~/bz2{0,1}$/){
		($dirOfFiles,$arrOfFiles)=unPack($xfile);
		if (exists $cfg{'UserInfo'}{'multi'} && $cfg{'UserInfo'}{'multi'} ){
			push(@xarr,@{$arrOfFiles});
		}else{
			$xfile=~s/.bz2{0,1}$//g;
			push(@xarr,abcc_rename($xfile));
		}
	}elsif ($xfile=~/(.(tar\.|t){0}gz)$/){
		print "Found $1|->\t$2|->\t$3|\n";
		my $sufx=$1;
		($dirOfFiles,$arrOfFiles)=unPack($xfile);
		print "in checkAndSanitze after unPack...$dirOfFiles and $arrOfFiles";
		if (exists $cfg{'UserInfo'}{'multi'} && $cfg{'UserInfo'}{'multi'} ){
			push(@xarr,@{$arrOfFiles});
		}else{
			$xfile=~s/$sufx//;
			push(@xarr,abcc_rename($xfile));
		}
	}elsif ($xfile=~/zip$/){
		#find all files in the zipfile
		# print "$unzip -l $xfile....\n";
		# my @features=split("\n",`$unzip -l $xfile`);
		($dirOfFiles,$arrOfFiles)=unPack($xfile);
		print `pwd`;
		print "returned $dirOfFiles and ". join ("\n",@{$arrOfFiles})." from archive\n";
		for (my $i=0;$i<=$#{$arrOfFiles};$i++){
			print "$i...working on features $$arrOfFiles[$i]....\n";
			if ($$arrOfFiles[$i]=~/(\S+)$/){
				my $old=$1;
				my $newname=abcc_rename($old);
				system ("mv $workdir/$old $workdir/$newname\n") and print "[INFO] mv $workdir/$old $workdir/$newname\n" if ($old ne "$newname" && -e $workdir/$old);
				push (@xarr,$newname);
			}else{
				die "wtf $$arrOfFiles[$i]...\n";
			}
		}
	}elsif ($xfile=~/(\.tar$)/){
		($dirOfFiles,$arrOfFiles)=unPack($xfile);
		if (exists $cfg{'UserInfo'}{'multi'} && $cfg{'UserInfo'}{'multi'} ){
			push(@xarr,@{$arrOfFiles});
		}else{
			$xfile=~s/.tar//;
			push(@xarr,abcc_rename($xfile));
		}
	}elsif($xfile=~/(tar.|t)gz$/){
		print STDERR "haven't coded for $1 yet at $0 LN".__LINE__."\n";
	}else{
		print STDERR "Haven't coded for this suffix($xfile)...skipping\n";
	}
	if (exists $cfg{'UserInfo'}{'multi'} && $cfg{'UserInfo'}{'multi'} ){
		for (my $j=0;$j<=$#xarr;$j++){
			my %tmpconfig ;
			tie %tmpconfig, 'Config::IniFiles', (-file =>"$workdir/config.ini");
			$tmpconfig{'UserInfo'}{'partOfmulti'}=$cfg{'UserInfo'}{'file'};
			$tmpconfig{'UserInfo'}{'file'}=$dirOfFiles."/".$xarr[$j];
			$tmpconfig{'UserInfo'}{'multi'}=0;
			$tmpconfig{'Target_Info'}{'base_dir'}.="-FN$j";
			$tmpconfig{'Target_Info'}{'label'}.="-FN$j";
 			tied( %tmpconfig )->WriteConfig( "$dirOfFiles/config.ini-$j" );
 			system ("$perl $0 -f $opt_f -c $dirOfFiles/config.ini-$j &\n");
 			print "Running $perl $0 -f $opt_f -c $dirOfFiles/config.ini-$j\n";
 			#write to mySQL
		}
		print "Finished launching ". ($#xarr+1). " jobs for separate analysis...\nDone!\n". `date`;
		exit;
	}
	
	return (\@xarr);
	
}
sub unPack{
	my $xfile=shift;my $newdir;
	if (exists $cfg{'UserInfo'}{'multi'} && $cfg{'UserInfo'}{'multi'}==1){
		$newdir=`dirname $xfile`;chomp $newdir;$newdir.="/tmp$cfg{'UserInfo'}{'date'}";
		if (-e $newdir){
			system ("rm -f $newdir/*\n");
		}else{
			print "making a directory....$newdir...\n";
			mkdir ($newdir);
		}
		print "[Info] cp $xfile $newdir\ncp $xfile $newdir\n";
		system ("cp $xfile $newdir\n");
		$xfile=`ls $newdir/`;chomp $xfile;
		if ($xfile!~/^\//){
			$xfile=$newdir."/$xfile";
		}
		print "The new file is $xfile!\n";
	}
	sendMsg("ERROR, UserInfo FileName", "This ($xfile) is going to cause problems on unix, rename and resubmit\n", "") if ($xfile=~/\s/);
	if($xfile=~/^\//){
		$newdir=`dirname $xfile`;chomp $newdir;
		chdir $newdir;
	}
	my $sfx='';
	if ($xfile=~/(ab1|scf|trace|txt)$/){
		return 1;
	}elsif ($xfile=~/(tar.gz|tgz)$/ ){
		$sfx=$1;
		system ("$gunzip < $xfile | $tar xvf -\n");
	}elsif ($xfile=~/tar$/){
		$sfx='tar';
		system ("$tar -xf $xfile\n");
	# }elsif ($xfile=~/(bzip|bz2)$/){
	# 	$sfx=$1;
	# 	system ("$bzip $xfile\n");		
	}elsif ($xfile=~/(gz|z)$/i){
		$sfx=$1;
		system ("$gunzip $xfile\n");

	}elsif ($xfile=~/(zip)$/i){
		$sfx=$1;
		print "$unzip -o $xfile\n";
		system ("$unzip -o $xfile\n");
	}elsif ($xfile eq ''){
		die "haven't coded yet\n";
		#need to unpack by going into each of the directories and unpacking one at a time
	}else{
		sendMsg( "ERROR, USER INPUT extension", "The user has specified a unknown package ($xfile)...please code", "");
	}
	my @arrayOfCompressedFiles=();
	if (exists $cfg{'UserInfo'}{'multi'} && $cfg{'UserInfo'}{'multi'}==1){
		@arrayOfCompressedFiles=`ls $newdir|grep -ve $sfx` if ($sfx);
		# print "This is funny!\n".  join ("|\n|",@arrayOfCompressedFiles) ."\n---------------\n";
	}else{
		push(@arrayOfCompressedFiles,`ls $newdir |head -n1`);
	}
	chdir("$workdir");#changed this from original directory on 12/12/11
	return ("$newdir",\@arrayOfCompressedFiles);
}

sub monitor {
	my $grid_id=shift;
	my $start_time=`date "+%s"`;chomp $start_time;my $curr_time=$start_time;
	my $MONITOR_LEN=600;$start_time+=$MONITOR_LEN;#roughly 1 hour
	my $notDone=1;
	unless ( $start_time<=$curr_time ){
		$notDone=`qstat | grep $grid_id`;chomp $notDone;
		if ($notDone){
			$curr_time=`date "+%s"`;
			sleep (int($MONITOR_LEN/10));
		}
	}
	return $notDone;
}

sub reorder{
	my $fh=shift;
	my $flag=0;
	print LOG "In Reorder...\n";
	if (! -e $dbinfofile){
		print LOG "$dbinfofile does not exist\n";
		my $mysql_exe=`which mysql`;chomp $mysql_exe;
		if ( $mysql_exe != ''){
			$dbinfofile="$web_dir/database.info";
			system ("$mysql_exe -h sqldb1.abcc -u abccruser -pabccrpwd <$web_dir/sql >$dbinfofile\n");
			open (DBDUMP,"<$dbinfofile") or return;
		}else{
			return;
		}
	}
	open (DBDUMP,"<$dbinfofile") or $flag=1;my $max=-1;my %priOrder;
	print LOG "Successfully opened a database file to rearrange ($dbinfofile)\n";
	my $nidx=my $priority=-1;
	while (<DBDUMP>){
		my @elements=split("\t",$_);chomp $elements[$#elements];
		if ($_=~/^database\_id/){
			for (my $i=0;$i<=$#elements;$i++){
				if ($elements[$i]=~/database\_category\_idx/){
					$priority=$i;
					last;
				}elsif ($elements[$i]=~/database\_annvr\_name/){
					$nidx=$i;
				}
			}
		}else{
			die "headers not found" if ($nidx<0 || $priority<0);
			$priOrder{$elements[$nidx]}=$elements[$priority];
			$max=$elements[$priority] if ($max<$elements[$priority]);
		}
	}
	close DBDUMP;
	$max++;
	my %ordered;my $chkcount=$#files;
	#print Dumper (\%priOrder);
	foreach my $dbname (@files){
		if ($dbname=~/$refver\_(.*)\.txt/ ){
			my $db=$1;
			if (exists $priOrder{$db}){
				$ordered{$priOrder{$1}}.="$dbname\n";
			}else{
				print "$db not exist in priOrder hash!\n";
				$ordered{$max}.="$dbname\n";
			}
		}else{
			print "$opt_o\n";
			$ordered{$max}.="$dbname\n";
		}
	}
	#print Dumper (\%ordered);
	@files=();
	foreach my $key (sort {$a<=>$b} keys %ordered){
		print $fh "$ordered{$key}";
	}
}
