#!/usr/bin/perl
use strict;
use FindBin;
use Data::Dumper;
use Getopt::Std;
umask(0000);
my $cmdline="$0 ". join (" ",@ARGV);
use vars qw( $opt_d $opt_a $opt_k $opt_E $opt_f $opt_l $opt_x);
getopts("d:a:kEflx");#database and avia id, respectively
my $proc_dir='/bioinfoC/AVA/FDI/';
my $web_scripts=`dirname $0`;chomp $web_scripts;
if ($web_scripts!~/avia\d+/){
	$proc_dir=$web_scripts;
	$proc_dir=~s/scripts(\.dir){0,1}/data\//g;
}
my $user='www-avial';
my $dbs_fn='/bioinfoC/AVA/FDI/searchTheseDBs.txt';
my $opt_o= 'hg19';
my $orgdb="humandb";
if ($opt_o!~/hg/i){$orgdb="mousedb";}
my $SEQIDX_DIR ="/SeqIdx/annovardb/$orgdb";
my $bin="/bioinfoC/hue/annovar/annovar_dev_forFDI";chomp $bin;
die "[ERROR] Your database directory $SEQIDX_DIR does not exist or is not a directory\n"  if ( (defined $SEQIDX_DIR) && (!-e $SEQIDX_DIR || !-d $SEQIDX_DIR) );
my @files;
my $useEnsembl;
#### make the file for ANNOVAR input
my $server=`uname -a |cut -f2 -d ' '`;chomp $server;
my $id=($opt_a)?$opt_a:`date "+%S"`;chomp $id;
$opt_d=~s/\s/_/;
if (!defined $opt_d ){
	$proc_dir.="$id";
	chdir ("$proc_dir");
	print LOG "Changing directories $proc_dir\n";
	open (LOG,">>fdi_wrpr.log") or die __LINE__.":Cannot open log file for fdi wrapper\n";
	my @var_arr;
	open (STDERR,">&LOG") ;
	printAlready(1);
	exit if ($opt_x);
	if (!-e 'searchTheseDBs.txt'){
		system ("cp $dbs_fn $proc_dir\n") ;
	}
	if (  !$opt_k && ! -e 'annovar.bat' ){
		if ($opt_E || ( -e "config.ini" && `grep 'useEnsembl=on' config.ini | wc -l` > 0 )){
			$useEnsembl=' -E ';
		}
		my $cmd="perl $bin/annovar_qsub_wrpr.pl -f searchTheseDBs.txt $useEnsembl -G -i $id -o $opt_o -W ";
		if (!$opt_l){
			$cmd.= ' -C ';
		}
		if ($opt_f){
			$cmd=~s/\-W//g;
		}
		my $success=runAndMonitor($cmd,'annovar');
		system ("chmod 777 $proc_dir -R\n");
	}else{
		die ""
	}
}elsif ($opt_l){ 
}elsif ($opt_d){ 
	$proc_dir.=$id;
	if (! -e $proc_dir){die "wtf??($proc_dir??\n";
		open (LOG,">>/bioinfoC/AVA/FDI/DUPLICATE_ERRORS.txt") or die "Cannot open /bioinfoC/AVA/FDI/DUPLICATE_ERRORS.txt for fdi wrapper\n";
		print LOG `date`;
		print LOG "\t[ERR]$proc_dir(AND OPT_A:$opt_a??) does not exist!\n\t[CMD]$cmdline\n\tExiting...\n\n";
		close LOG;
		exit;
	}
	chdir ($proc_dir);
	my %annot;
	if ($opt_f){
		open (FN,"<$proc_dir/$id") or die "Canot open $proc_dir/$id at line".__LINE__."\n";
		while (<FN>){
			my $var=join(":",split("\t",$_));chomp $var;
			$annot{$var}='';
		}
		close FN;
	}else{
		foreach my $var (split(",",$ARGV[0])){
		    $annot{$var}='';
		}
	}
	open (LOG,">>fdi_wrpr.log") or die "Cannot open log file for fdi wrapper\n";
	open (STDERR,">&LOG") ;
	print LOG  "LINE".__LINE__. Dumper (\%annot);
	#read the file
	print LOG "Running at " .`date`;
	print LOG "Changing directory to $proc_dir for $opt_d\n";
	my $timeout=`date "+%d%H%M%s"`;chomp $timeout;$timeout+=6000;#1hour=6000
	my $fail=0;my $searchdb="$proc_dir/../MASTER.database.out";
	if (!$opt_k){
		$searchdb="$proc_dir/$opt_a.hg19_$opt_d";
		# if ($opt_d=~/Funseq/i){
		# 	$searchdb="$proc_dir/$opt_a.$opt_d";
		# }
		# print "Looking for $searchdb\n";
		if ($searchdb=~/zygosity/){
			if (-e "ANNOVAR.input.hg19_zygosity"){
				$searchdb="$proc_dir/ANNOVAR.input.hg19_zygosity";
			}
		}
		until (-e "$searchdb" || $fail){
			sleep(15);
			my $curr_time=`date "+%d%H%M%S"`;chomp $curr_time;
			if ( $curr_time>$timeout){$fail=1;print LOG "Timed out\n";}else{
				print LOG "[$curr_time]Waiting for $searchdb to finish...\n";}
		}
		if ($fail){
			print "failed\n";
			exit 0;
		}
		my $count=0;
		open (FILE,"<$searchdb" ) or die "Cannot open $searchdb\n";
		while (my $line=<FILE>){
			print $line;#			print "$pos\t$arr[$#arr]\n";
		}
# 		if (!$count){#could not find the particular database, then run FDI wrapper that database
# 			print LOG "Doesn't look like the database $opt_d exists in the file...Running it...\nperl /bioinfoC/hue/annovar/current/fdi_wrpr.pl $opt_d '$ARGV[0]'\n...\n";
# #### REINSTATE AFTER testing!!!!
# 			# system ("perl /bioinfoC/hue/annovar/current/fdi_wrpr.pl $opt_d '$ARGV[0]'\n");
# 			print LOG "Completed running fdi_wrpr for $opt_d\n";
# 		}
	}else{
		print LOG "opening $searchdb for $opt_d\n";
		print LOG "LINE".__LINE__. Dumper (\%annot);
		open (FILE,"<$searchdb" ) or die "Cannot open $searchdb\n";
		while (my $line=<FILE>){
			chomp $line;
			next if ($line!~/\b$opt_d\b/);
			my @arr=split("\t",$line);
			my $pos=join (":",@arr[0..4]);
			print  "$pos\t$arr[$#arr]\n" and $annot{$pos}=1 if (exists $annot{$pos} && !$annot{$pos});
		}
		close FILE;
	}
	close LOG;
	
}else{die "Don't know what I'm doing!\n";
	printAlready();
}

print STDERR "done!\n";
sub runAndMonitor{
	my $cmd=shift;
	my $name=shift;
	open (BAT,">$name.bat" ) or die "Cannot open $name.bat\n";
	print BAT "#PBS /bin/bash\n#PBS_O_WORKDIR=$proc_dir\n#PBS -j oe -e $proc_dir/$name\_stderrout\ncd $proc_dir\n";
	print BAT "$cmd";
	close BAT;
	my $qsub_exe=`which qsub`;chomp $qsub_exe;
	if (!$qsub_exe && -e "/usr/local/bin/qsub"){
		$qsub_exe="/usr/local/bin/qsub";
	}elsif (!$qsub_exe){
		die "Could not submit to grid on node " . `uname -a` ."qsub_exe=$qsub_exe and !-e /usr/local/bin/qsub\n";
	}
	my $qsub_bin=`dirname $qsub_exe`;chomp $qsub_bin;
	$ENV{'PATH'}.=":$qsub_bin:/usr/local/bin";
	system ("chmod 777 $proc_dir/$name.bat 2>/dev/null\n");
	print LOG "Running command $cmd in runAndMonitor\n\t$qsub_exe -u $user $proc_dir/$name.bat\n";
	my $addon='';
	# if ($qsub_exe=~/annovar.bat/){#temporary fix 2014/08/18
	# 	$addon="-l host=fr-s-hpc-a1-04,host=fr-s-hpc-a1-31,host=fr-s-hpc-a1-32,host=fr-s-hpc-a1-33,host=fr-s-hpc-a1-34,host=fr-s-hpc-a1-35,host=fr-s-hpc-a1-36,host=fr-s-hpc-a1-37,host=fr-s-hpc-a1-38 ";
	# }
	my $grid_id=`$qsub_exe -u $user $addon $proc_dir/$name.bat`;chomp $grid_id;
	print LOG "In runAndMonitor...$cmd($name.bat)...grid_id=$grid_id\n";
	if ($grid_id=~/(\d{6,})/){
		my $targets="$qsub_bin/qstat | grep $1 ";
		my $timeout=`date "+%d%H%M%S"`;chomp $timeout;$timeout+=3600;#1hour=6000
		my $done=1;
		print LOG "$targets($timeout)\n";
		my $curr_time;
		until (`$targets` eq '' || !$done){
			sleep (15);
			$curr_time=`date "+%d%H%M%S"`;chomp $curr_time;
			if ( $curr_time>$timeout){$done=0;print LOG "Timed out ($curr_time>$timeout)\n";}
		
		};
		print LOG "Done running on grid($curr_time vs $timeout)\n";
		return $done ;
	}else{
		print LOG "LOG:Could not submit to grid ($name.bat) for  $cmd and ($grid_id)CMD :$qsub_exe -u $user $addon $proc_dir/$name.bat\n".`$qsub_exe -u $user $addon $proc_dir/$name.bat`;
		die "[ERR] Could not submit to grid ($name.bat) for  $cmd and ($grid_id)CMD :$qsub_exe  -u $user $addon $proc_dir/$name.bat\n\n" . `$qsub_exe -u $user $addon $proc_dir/$name.bat`;
	}
	return 0;
}

sub printAlready{
	my $printtofile=shift;
	$printtofile=0 if ($opt_x);
	if (!$opt_f){  # this file may be written in FDIUtils if it is a large file
		my @var_arr;
		open (FILE,">$proc_dir/$id") or die __LINE__.":Cannot open $id\n";
		open (INPUT,"<$proc_dir/$id.txt") or die "cannot open $proc_dir/$id.txt in $0 directory ($proc_dir)\n";
		print LOG "opening $id.txt ($0)\n";
		while (my $line=<INPUT>){
			chomp $line;
			if ($line!~/^#/){
				$line=~s/^chr//;
				my @arr=split("\t",$line);
				my $var=join (":",@arr[0..4]);
				print "$var\t$var\n";
				print FILE join ("\t",@arr[0..4])."\n" if ($printtofile);
			}
		}
		close FILE;
		close INPUT;
		print LOG "\tDone printAlready\n";
	}else{die __LINE__."\n";
		print LOG "Opening file from FDIUtils\t$cmdline\n";
		open (FILE,"<$proc_dir/$id") or die "Cannot open\n";
		while (my $var=<FILE>){
			chomp $var;next if ($var=~/^\s{0,}$/);
			my @arr=split("\t",$var);$var=join (":",@arr[0..4]);
			print "$var\t$var\n";
		}
		close FILE;
	}
}
