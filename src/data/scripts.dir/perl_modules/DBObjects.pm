use DBI;
sub getExonByPos{
	my $pos=shift;
	my $gene=shift;
	my $dbh=DBI->connect("dbi:mysql:GFF_Human_36_GRID:sqldb2.abcc","ABCC_GB_ROUser","abcC") || die "Could not connect to the database\n";
	my $sql="select distinct strand from I_Exons where gene='$gene'";
	my $sth=$dbh->prepare ($sql);
	my %tmp_hash;
	$sth->execute();
	my $strand=$sth->fetchrow_array();
	if ($strand eq "-"){
		$sql="select exon from I_Exons where gene='$gene' and start>=$pos and stop<=$pos";
	}else{
		$sql="select exon from I_Exons where gene='$gene' and start<=$pos and stop>=$pos";
	}
	$sth=$dbh->prepare ($sql);
	$sth->execute();
	$results=$sth->fetchrow_array();
	$dbh->disconnect();
	return -1 if ($results eq "");
	return $results;
}
1;
