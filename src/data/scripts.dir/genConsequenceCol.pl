#!/usr/bin/perl
use strict;
use Getopt::Std;
use Data::Dumper;
umask(0000);
print "$0 " .join (" ",@ARGV)."\n";
use vars qw( $opt_f $opt_a $opt_h $opt_H $opt_o $opt_d $opt_g $opt_G $opt_l $opt_k $opt_v );
getopts("f:H:o:d:G:l:v:gakh");
#  GET vars
my $usage=qq(
	$0 
	[REQUIRED]
		-f <AVIA OUTPUT FN>
	[OPTIONAL]
		-o <output filename>
		-a <if specified,do not add as column in original file> 
			DEFAULT: replace original file with a file with col of consequence
		-d <Number between 1-5 indicating the number of protein coding algorithms that will report "damaging">
			DEFAULT: 2 or more databases call damaging
		-g <generate gene list as well>
		-G <alternative gene list filename output> DEFAULT: genelist
		-l <label>
		-v <genome version> default : hg19
	);

print "$usage\n" and exit if (!defined $opt_f || $opt_h);
my $avia_output_fn=$opt_f;
my $headerFound=0;
my (%headers,%annot,%genes);
# Read file and continue
$opt_o||="$opt_f.tmp";
$opt_G||="genelist";
$opt_v||="hg19";
my $label=(defined $opt_l)?$opt_l:"index";
my $maxDam=($opt_d)?$opt_d:2;
my $consequence='';
if (defined $opt_g){
	open (GENE,">$opt_G") or die "Cannot open genelist for writing ($opt_G)\n" ;
	open (GENE2,">$opt_G.consequence") or die "Cannot open genelist for writing ($opt_G.consequence)\n";
	open (GENE3,">$opt_G.codingOnly") or die "Cannot open genelist for writing ($opt_G.codingOnly)\n";
}else{
	die;
}
my $db_info_hashref=getAVIA();
my ($exac_col);
open (OUT,"> $opt_o") or die "Cannot open file $opt_o for writing\n";
open (HTML,">$label.html") or die "cannot open file\n";
open (FILE,"<$avia_output_fn") or die "cannot open your input file $avia_output_fn for reading...\n";
print HTML "<table id='example' border='1' class=\"display\"><thead>";
my $count=0;my $fs_col=my $fs='';my $fs_total=0;
my $summaryFound=0;my $origver='';
while (my $line=<FILE>){
	chomp $line;
	$consequence='';
	my @colElements=split("\t",$line);
	if ($headerFound){
		for (my $i=0;$i<=$#colElements;$i++){
			if ($colElements[$i]=~/(damaging|med|high|disease causing|deleterious)/i && $headers{$i}=~/(sift|pp2|ma$|mt$|Mutation Taster|Mutation Assessor|Polyphen|Provean)/i){
				$consequence.="X";
				# print "found $1 and $consequence\n";
			}elsif($colElements[$i]=~/SIG=(probable-)*pathogenic/i && $headers{$i}=~/(ClinVar|Clinical Var)/i){
				$consequence.="V";
			}elsif($colElements[$i] ne '-' &&  $headers{$i}=~/((C)osmic|(P)TM$)/i && $colElements[$i]!~/ERR/){
				#for some reason testing on 9/11/14, this did not work any longer
				#$2 was empty even though $1=Online Mendelian and $2='';
				# $consequence.=uc($1);
				my $h=$1;
				# print "working on $i and $h and $colElements[$i]\n";<STDIN>;
				if($h=~/(P)TM$/i){
					$consequence.=uc($1);
				}elsif($h=~/(C)osmic/i){
					$consequence.=uc($1);
				}elsif($h=~/translational/i){
					$consequence.='P';
				}elsif($h=~/(OMIM|Online Mendelian)/i){
					$consequence.='O';
				}else{
					# print "what's this $h and $colElements[$i]?\n";die;
				}
				# print "my consequence:" .$consequence."\n";
			}elsif( $headers{$i}=~/(FunSeq|coding.score)/i){
				my @arr=split(";",$colElements[$i]);
				my ($fs1,$fs2)=split(",",$fs_col);
				$consequence.="F" if ($arr[$fs1]>=4 || $arr[$fs2]>=1.5);
			}elsif($summaryFound){
				$consequence=$colElements[0];
			}elsif ($colElements[$i]!='-'){
				# print "passing ($i)$headers{$i} and $colElements[$i]\n" and <STDIN> if (exists $headers{$i});
			}
		}
	}elsif ($line=~/(^#|Variant ID|Summary)/){
		$consequence="#Summary";
		for (my $i=0;$i<=$#colElements;$i++){
			print "working on $colElements[$i] ($i)\n";
			if ($colElements[$i]=~/(sift|pp2|ma$|mt|Mutation Taster|Mutation Assessor|Polyphen|Provean|PTM$|Cosmic|ClinVar|Clinical Var|translational|FunSeq|coding.score;|ExAC.*)/i){
				$headers{$i}=$1;
				if ($colElements[$i]=~/(FunSeq|coding.score;)/i){
					my @fs_cols=split(";",$colElements[$i]);$fs=$i;
					for (my $k=0;$k<=$#fs_cols;$k++){
						$fs_col.="$k,"  if ($fs_cols[$k]=~/coding.score/);
					}
					$fs_total=$#fs_cols;
				}elsif ($colElements[$i]=~/(ExACv)/i){
					$exac_col=$i;
				}
			}elsif ($colElements[$i]=~/(hg\d+_origPos|ANNOVAR annot|Rel pos|Gene$|Annot Feat|Comment|^Chr$|Gene Ori)/){
				my $header1=$1;
				$annot{$header1}=$i;
				$origver=($header1=~/(hg\d+_origPos)/)?$1:$origver;
			}
		}
		print  "I'm here: ". Dumper (\%annot);
		$headerFound=1;# and print Dumper (\%headers) and print Dumper (\%annot) and <STDIN>;
	}else{
		die "Don't know how to parse this as there is no header!\n";
	}
	$consequence=~s/X{$maxDam,}/D/g;
	$consequence=~s/X//g;
	if ($opt_g){
		my $add='';
		my @gene_arr=();#we do this for multiple genes
		# if ($colElements[$annot{"Gene"}]=~/(([^\(]*)\(.*\)|[^\(]*)/){#splicing annot
		if ($colElements[$annot{"Gene"}]=~/\(/){
			my @matches=split(/[\(\)]/,$colElements[$annot{"Gene"}]);
			foreach my $gene (split(/[\(\)]/,$colElements[$annot{"Gene"}])){#e.g TRIM39(NM_021253:exon3:c.453+2T>G,NM_172016:exon3:c.453+2T>G),TRIM39-RPP21(NM_001199119:exon1:c.453+2T>G)
				next if ($gene=~/(ENST|NM_)\d+:/);
				push(@gene_arr,split(',',$gene));
			}
			
		}elsif ($colElements[$annot{"Gene"}]=~/[,;]/){
			push(@gene_arr,split(/[,;]/,$colElements[$annot{"Gene"}]));
		}else{
			chop ($colElements[$annot{"Gene"}]) if ($colElements[$annot{"Gene"}]=~/;$/);
			push(@gene_arr,$colElements[$annot{"Gene"}]);
		}
		# print "working on $consequence|". $colElements[$annot{"Gene"}]."|..." .$colElements[$annot{"ANNOVAR annot"}] .join (",",@gene_arr);<STDIN>;
		# For exonic mutations
		if ($colElements[$annot{"ANNOVAR annot"}]=~/(intronic|UTR|exonic|splic)/){
			if ($consequence ne '') {
				$add='consequence';
				# print "Adding consequence...\n";
			}
			if ($colElements[$annot{"ANNOVAR annot"}]=~/(exonic|splic)/){
				$add.='codingOnly';
			}
		}elsif ($consequence ne ""){#if this consequence is important
			$add.='consequence';
			# print "Adding consequence...\n";
		}
		foreach my $gene(@gene_arr){
			next if ($gene=~/^\s{0,}$/);
			chop $gene if ($gene=~/[;,:]$/);
			$genes{$gene}{'all'}=1;
			if ($add=~/(consequence)/){
				$genes{$gene}{$1}=1;
			}
			if ($add=~/(codingOnly)/){
				$genes{$gene}{$1}=1;
			}
			
		}
	}
	# print Dumper (\%headers);die;
	
	$consequence=($consequence eq '')?"-":$consequence;
	my $descript='';
	 if (!$opt_a){
	 	my $last=(exists $annot{'Comment'})?$annot{'Comment'}:keys(%headers);
	 	my $first=(exists $annot{'Chr'})?$annot{'Chr'}:0;
	 	if ($first){
	 		if (-e "subtractive_headers.out" && $line=~/(\bChr\b|Variant ID)/) {
	 			@colElements=split("\t",`head -n1 subtractive_headers.out`);
	 			chomp $colElements[$#colElements];	
	 		}
	 		if ($fs ne ''){
	 			# print "This is my funseq??($fs)$colElements[$fs]\n";<STDIN>;
	 			if ($colElements[$fs] eq '-'){#FunSeq error; but must keep alignment intact
	 				$colElements[$fs]='';
	 				for (my $k=0;$k<=$fs_total;$k++){
	 					$colElements[$fs].="-\t";
	 				}
 				}else{
 					$colElements[$fs]=~s/;/\t/g;
 				}
	 		}
	 		if (join (":",@colElements[$first..($first+4)])=~/:\s+:/){
	 			# die "$first..$last\n";
	 			#the input was space delimited, not tab
	 			$colElements[$first]=~s/\s/:/g;chop $colElements[$first] if ($colElements[$first]=~/:$/);
	 			$consequence.="\t" .$colElements[$first]."\t".join ("\t",@colElements[0..($first-1)]);
 			}else{
 				if ($fs ne '' && $consequence=~/#/){
		 			# print "isn't this funseq ? ". $colElements[($fs+4)]."($fs)\n";
		 			$colElements[$fs]=~s/\t/\t#FunSeq2_/g;
		 			$colElements[$fs]=~s/^#/#FunSeq2_/;
		 			if ($colElements[$fs]=~/#FunSeq2_$/){
		 				$colElements[$fs]=~s/\t#FunSeq2_$//;
		 			}
		 			# print $colElements[$fs];<STDIN>;
		 		}
		 		if ($exac_col){
		 			if($consequence=~/#/){
		 				$colElements[$exac_col]=~s/.*/#ExACv3_AltAC_Hom\t#ExACv3_All_MAF\t#ExACv3_NFE_MAF\t#ExACv3_Highest_MAF\t#ExACv3_Ethnicity/;
	 				}elsif ($colElements[$exac_col]=~/:/){
	 					$colElements[$exac_col]=~s/:/\t/g;
	 				}else{
	 					$colElements[$exac_col]='';
	 					for (my $i=0;$i<5;$i++){
	 						$colElements[$exac_col].="-\t";
	 					}
	 					$colElements[$exac_col]=~s/\t$//;
	 				}
		 		}my $addon='';
		 		if ($origver && exists $annot{$origver}){
		 			$addon.="\t$colElements[$annot{$origver}]";
		 		}
		 		if (exists $annot{'Comment'}){
		 			$addon.="\t$colElements[$annot{'Comment'}]";
		 		}
	 			$consequence.="\t" .join (":",@colElements[$first..($first+4)]). "\t".join ("\t",@colElements[$annot{'ANNOVAR annot'}..$annot{'ANNOVAR annot'}+3,$annot{'ANNOVAR annot'}-1,0..$annot{'ANNOVAR annot'}-2]).$addon;
	 		}
	 		if ($consequence=~/#{0,1}Chr:#{0,1}Query Start:#{0,1}Query End:#{0,1}Allele1:#{0,1}Allele2/){#header
			 	$consequence=~s/#{0,1}Chr:#{0,1}Query Start:#{0,1}Query End:#{0,1}Allele1:#{0,1}Allele2/Variant ID/g;
			}else{
				$consequence=~s/#//;
			}
	 	}else{
	 		$consequence.="\t" .join ("\t",@colElements[0..$last]);
	 	}
	 }
	
	print OUT "$consequence\n";
	if ($consequence!~/^-/ || $opt_k){
		if ($consequence=~/^#/){
			$consequence=~s/&/&amp;/g;
			$consequence=~s/>/&gt;/g;
			$consequence=~s/</&lt;/g;
			my @headerArr=split("\t",$consequence);
			my $orig=$consequence;
			$consequence="\n<tr>";
			foreach my $id (@headerArr){
				# print "working on $id\n";
				my $nocmt=$id;$nocmt=~s/#//g;
				if ($nocmt=~/Funseq/i){
					$consequence.="<th><a title=\"". $db_info_hashref->{'FunSeq*'}{'description'}."\">".$nocmt."</a></th>\n";
				# }elsif($nocmt=~/ExAC/){
				# 	$consequence.="<th><a title=\"ExACv3\">ExACv3_$nocmt</a></th>\n";
				}elsif (exists $db_info_hashref->{$nocmt}){
					$consequence.="<th><a title=\"". $db_info_hashref->{$nocmt}{'description'}."\">".$db_info_hashref->{$nocmt}{'fulltitle'}."</a></th>\n";
				}else{
					$consequence.="<th>$nocmt</th>";
				}
			}
			$consequence.="</tr></thead><tbody>\n";
			# $consequence=~s/\t/<\/th><th>/g;
			# $consequence="<tr><th>".$consequence."</th></tr></thead><tbody>\n";
		}else{
			$consequence=~s/&/&amp;/g;
			$consequence=~s/</&lt;/g;
			$consequence=~s/>/&gt;/g;
			$consequence=~s/\t/<\/td><td>/g;
			$consequence="<tr valign='top' CLASS><td>$consequence</td></tr>\n";
			$count++;
			if ($count%2==0){
				$consequence=~s/CLASS/class=\"altclr\"/;
			}else{
				$consequence=~s/CLASS//;
			}

		}
		print HTML $consequence;
	}else{
		# die "skipping $consequence??\n";
	}
}
print HTML "</tbody></table>\n";
close FILE;close HTML;
# exit;
if ($opt_g){
	foreach my $gene (sort %genes){
		next if $gene eq 'Gene';
		next if ($gene=~/None/i);
		if (exists $genes{$gene}{'all'}){
			print GENE "$gene\n";
		}
		if (exists $genes{$gene}{'consequence'}){
			print GENE2 "$gene\n";
		}
		if (exists $genes{$gene}{'codingOnly'}){
			print GENE3 "$gene\n";
		}

	}
	close GENE;close GENE2;close GENE3;
}

close OUT;
if (!defined $opt_a && $opt_o eq "$opt_f.tmp"){
	if (-s $opt_o > 500){
		system ("mv $opt_o $opt_f\n");
		my $line=`head $opt_f -n1`;chomp $line;
		open (HEADERS,">subtractive_headers.out") or warn "cannot open subtractive_headers.out\n";
		print HEADERS "$line\n";
		close HEADERS;
	}else{
		print STDERR "[INFO] Could not add column to your input file...see tmp input file $opt_o\n";
	}
}else{
	my $line=`head $opt_o -n1`;chomp $line;
	open (HEADERS,">subtractive_headers.out") or warn "cannot open subtractive_headers.out\n";
	print HEADERS "$line\n";
	close HEADERS;
}


sub getAVIA{

    #my $db='avia_abcc_dbdev';
    #my $user="avia_db_admin";
    #my $pwd='EA8J9br4RGKXm';
    #my $dev='dev';
    #my $host = 'sqldb1.abcc.ncifcrf.gov';
    #my $sqlfile=`dirname $0`;chomp $sqlfile;$sqlfile.='/sql';
    #my @arr=split("\n",`mysql -h $host -u $user -p$pwd <$sqlfile`);
    my %hash;
    #foreach my $line(@arr){
    # 	chomp $line;
    #	next if ($line=~/^\s+$/);
   # 	my @lineArr=split("\t",$line);
   # 	$hash{$lineArr[0]}{'fulltitle'}=$lineArr[1];
    #	$hash{$lineArr[0]}{'description'}=$lineArr[2];
   # 	$hash{$lineArr[1]}{'fulltitle'}=$lineArr[1];
    #	$hash{$lineArr[1]}{'description'}=$lineArr[2];
    #}
    return (\%hash);
}
