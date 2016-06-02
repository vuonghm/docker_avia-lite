#/usr/local/bin/perl

# Author: Uma Mudunuri
# Date: 07/07/2008
# Description: perl script for updating the edge weights
# the weights are read from a tab delimited text file 
# line format: input output weight
# see the samples directory for a sample edge weight file
# input file: edgeWts.txt and fdi.xml
# output file: modified fdi.xml

use strict;
use XML::LibXML;
use XML::Twig;

my $wtFile = "edgeWts.txt";
my $xmlFile = "fdi.xml";

open(WTFILE,"$wtFile") || die("Cannot open $wtFile for reading \n");
my %wts;

while(<WTFILE>) {
    chomp $_;
    my ($input, $output, $wt) = split('\t',$_);
    $wts{$input}{$output} = $wt;
}
    
my $twig = XML::Twig->new();
$twig->parsefile($xmlFile);

my $root = $twig->root;

foreach my $input ($root->children('input')) {
    my $in = $input->att('id');
    foreach my $output ($input->children('output')) {
	my $out = $output->att('id');
	foreach my $child ($output->children) {
	    if($child->name eq "weight") {
		$child->set_text($wts{$in}{$out});
	    }
	}
    }
}

open(XMLFILE,">$xmlFile") || die("Cannot open $xmlFile \n");
$root->set_pretty_print('indented');
$root->print(\*XMLFILE);

close WTFILE;
close XMLFILE;
