#!/usr/bin/perl
use strict;
sub getFromCfg{
	my $cfg_href=shift;
	system ("rm genelist\n") if ( -e "genelist");
	if ($$cfg_href{'UserInfo'}{'abcc_genelists'} ne ''){
		my @arr=split(",",$$cfg_href{'UserInfo'}{'abcc_genelists'});
		foreach my $data (@arr){
			chomp $data;
			system ("cat /SeqIdx/circosdb/genelists/$data.txt >>genelist\n") if (-e "/SeqIdx/circosdb/genelists/$data.txt");
		}
	}
	if ($$cfg_href{'UserInfo'}{'genelists'}){
		my @arr=split(",",$$cfg_href{'UserInfo'}{'genelists'});
		foreach my $data (@arr){
			chomp $data;
			system ("cat $data >>genelist\n") if (-e "$data");
		}
	}
	if (-e "genelist" && !-z "genelist"){
		system ("mv genelist .genelist;sort -u .genelist >genelist");
		return 1;
	}else{
		return 0;#
	}
}
sub getGeneListHash{
	my $xfile=shift;#enter the filename
	if (!-e $xfile){
		return -1;
	}
	my %hash;
	open (FILE,"<$xfile") or die "Cannot open your genelist $xfile in parse.pm for reading\n";
	while (my $gene=<FILE>){
		chomp $gene;
		$gene=uc($gene);
		if ($gene=~/\((\w+)\)/){
			$hash{$1}='';
		}elsif ($gene!~/^[A-Z0-9\-]*$/i){
			chop $gene;
			if ($gene!~/^[A-Z0-9\-]*$/i){
				print "Don't know what this is $gene\n";next;
			}
		}
		$hash{$gene}='';
	}
	close FILE;
	return (\%hash);
}
1;
