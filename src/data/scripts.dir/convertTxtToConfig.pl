#!/usr/local/bin/perl
use strict;
use FindBin;
use lib "$FindBin::Bin/";
use lib "$FindBin::Bin/perl_modules/Config-IniFiles-2.38";
use lib "$FindBin::Bin/perl_modules";
use Config::IniFiles;
use Getopt::Std;
use Data::Dumper;
use vars qw( $opt_f $opt_o );
 getopts("f:o:t:");
 my $ADMIN="vuonghm\@mail.nih.gov";
 my $perl=`which perl`;chomp $perl  ; if ($perl eq ''){die "cannot find perl exe\n";}
my $usage=qq(
	$0 -f <input file from web>
	OPTIONAL
		-o <output cfg>
);
my $out_fn;
die "$usage\n" if (!defined $opt_f);
if (defined $opt_o){
	$out_fn=$opt_o;
	print STDERR "[WARN] - Overwriting current $out_fn\n" if ( -e $out_fn && !-z $out_fn )
}else{
	$out_fn="config.ini";
}
my $date=`date "+%Y%m%d"`;chomp $date;
#################Variable Declarations ####################
my $BASE_DIR;
my $bin=`dirname $0`;chomp $bin;
################read in config ######################
my $stub_cfg="$bin/config.stub";
if ( ! -e $stub_cfg){
	$stub_cfg="/users/abcc/vuonghm/scripts.dir/reseq/config.stub";
	die "BOTH STUB configs do not exist\n" if (! -e $stub_cfg);
}
my %cfg;
tie %cfg , 'Config::IniFiles', (-file =>"$stub_cfg");
$bin=$cfg{'Utilities'}{'scripts_dir'};
################Read in user inputs########################
my $web_input=$opt_f;
open (WEB, "<$web_input") || die "Cannot open [$web_input]\n";
my @keys=('Target_Info','Viewer','SNPParams','QC','StudyParams','UserInfo');
FILE: while (<WEB>){
	my ($key,$data)=split ("=",$_);chomp $data;
	#clean if it has the notation: section.parameter
	if ($key=~/(\w+)\.(\w+)/){
		$key=$2;
	}
	my $found=0;
	if ($key=~/db\_ver/ && $data=~/^([A-Za-z0-9]*)\_(\d+)/){
		$cfg{'Target_Info'}{'Organism'}=$1;
		$cfg{'Target_Info'}{'db_ver'}=$2;
		next FILE;
	}
	foreach my $keyEl (@keys){
		if (exists $cfg{$keyEl}{$key}){
			$data=~s/[\s\t]//g;
			$cfg{$keyEl}{$key}=$data;
			$found=1;
			last;
		}
	}
	if ($found==0){
		$cfg{'UserInfo'}{$key}=$data if (!exists $cfg{'UserInfo'}{$key});
#		?print STDERR "$key not found in config.stub...adding it\n";
	}
}
close WEB;
$BASE_DIR=$cfg{'Target_Info'}{'base_dir'};
#system ("mv $web_input complete/\n");
$cfg{'Target_Info'}{'base_dir'}=$BASE_DIR."/".$cfg{'Target_Info'}{'label'};
if ((-e $cfg{'Target_Info'}{'base_dir'}) && ($cfg{'Target_Info'}{'analysistype'} ne "add")){
	#if the base directory exists and the user did not want to add to the existing run, 
	#rename base dir and use it
	system ("rm $cfg{'Target_Info'}{'base_dir'}/* -f\n");
}
if ($cfg{'Target_Info'}{'db_ver'} != 36){
	#change the sift version
	$cfg{'Databases'}{'sift_coding_db'}=~s/36/$cfg{'Target_Info'}{'db_ver'}/;
	$cfg{'Databases'}{'sift_var_db'}=~s/36/$cfg{'Target_Info'}{'db_ver'}/;
	$cfg{'Databases'}{'DAS_db'}=~s/36/$cfg{'Target_Info'}{'db_ver'}/;
	$cfg{'Databases'}{'GRID_db'}=~s/36/$cfg{'Target_Info'}{'db_ver'}/;
}
if ($cfg{'Target_Info'}{'Organism'}!~/Human/i){
	my $org='Human';#getOrg($cfg{'Target_Info'}{'Organism'});
	$cfg{'Target_Info'}{'alias'}=$cfg{'Target_Info'}{'gene'};
	$cfg{'Target_Info'}{'gene'}="$org".$cfg{'Target_Info'}{'gene'};
}

################Write out config############################
 tied( %cfg )->WriteConfig( "$out_fn" );
exit;
sub getOrg{
	my $organism=shift;
	my %org=(
		'Mouse'=>'M-',
		'Human'=>'',
		'Chimp'=>'C-',
		'Cow'=>'B-',
		'Rat'=>'C-',
		'Horse'=>'E-',
		'Dog'=>'D-'
		);
	if (exists $org{$organism}){
		return $org{$organism};
	}else{
		die "$organism does not exist in hash at LN".__LINE__."\n";
	}
}
sub SAFE_PROCESS {
	my $cmd=shift;
	my $line_nbr=shift;
	chomp $cmd if ($cmd=~/\n$/);
	eval {
		system ("$cmd 2>&1\n");	
	};
	if ($?){
		sendMsg ("ERROR, PROCESSING", "execution of $cmd from $0 at line $line_nbr FAILED\n$?");
		die;
	}else{
		return;
	}
}
