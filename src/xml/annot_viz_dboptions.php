<table id="formatted" >
<tr><td colspan="4">
	<table width="100%" >
		<tr><td colspan="4">By default, your variants will be annotated using Protein coding algorithms under "Protein Coding". Click on options below to customize your annotations. Expand/Collapse any category by clicking on the arrows. </td></tr>
		<tr>
			<td >
				<button type="button" onclick="checkAllAnnot('input')" />Check All Annotation Databases</button>
				<input type="hidden" id="checkall" value="false"/>
				--or--
				<button type="button" onclick=expandCategories() >Expand/Collapse All Categories to Customize</button>
				<input type="hidden" id="expanded" value="0" />
			</td>
		</tr>
	</table>
</td></tr>

<?php 

//<tr><td>&nbsp;<img width="15" height="15"  src="/modules/faq/library/images/send.gif" />&nbsp;Check this column to add each database in each category&nbsp;&nbsp;</td></tr>
	$db_arr=array("Protein Coding"=>array (
				"siftv63"=>array("exonic","<a href=\"http://sift.jcvi.org\" target=\"_blank\" >SIFT Scores w/Predictions for SNPs Only</a> ",'1'),
				"runIndelNMD"=>array("exonic","SIFT NMD Prediction for Indels Only<br /><em>**takes about 20-30 minutes for ~1500 Indels</em>",''),
				"ljb26_pp2hvar"=>array("exonic","<a href=\"http://genetics.bwh.harvard.edu/pph2\" target=\"_blank\" >Polyphen2 Scores w/Predictions <br /><em>**based on Human Var set</em></a>","1"),
				"ljb26_pp2hdiv"=>array("exonic","<a href=\"http://genetics.bwh.harvard.edu/pph2\" target=\"_blank\" >Polyphen2 Scores w/Predictions <br /><em>**based on Human Div set</em></a>","1"),
				"ljb26_mt"=>array("exonic","<a href=\"http://www.mutationtaster.org/\" title=\"Database of Mutation Taster, which is a program that evaluates disease-causing potential of sequence alterations\" target=\"_blank\">Mutation Taster</a>","1"),
				// "ljb_all"=>array("exonic","<a href=\"http://www.openbioinformatics.org/annovar/annovar_db.html\">Combined Scores from whole-exome SIFT, PolyPhen, PhyloP, LRT, MutationTaster, GERP++ scores from ANNOVAR</a>","1"),
				"ljb26_ma"=>array("exonic","<a href=\"mutationassessor.org/\" title=\"Database of Mutation Assessor\" target=\"_blank\">Mutation Assessor</a>","1"),
				"Provean_v1_1"=>array("exonic","<a href=\"provean.jcvi.org/\" title=\"Protein Variation Effect Analyzer\" target=\"_blank\">Pre-computed Provean v1.1 Scores from dbSNP</a>","1"),
				"ljb26_cadd"=>array("exonic","<a href=\"http://cadd.gs.washington.edu//\" title=\"Whole Genome Scores for scoring the deleteriousness of single nucleotide variants as well as insertion/deletions variants in the human genome.\" target=\"_blank\">Combined Annotation Dependent Depletion</a>","1"),
				"ljb26_fathmm"=>array("exonic","<a href=\"http://fathmm.biocompute.org.uk//\" title=\"Prediction scores for functional consequences for coding and non-coding variants\" target=\"_blank\">FATHMM</a>","1"),
				"ljb26_vest"=>array("exonic","<a href=\"http://www.cravat.us/\" title=\"Variant Effect Scoring Tool (VEST), a supervised machine learning-based classifier, to prioritize rare missense variants with likely involvement in human disease.\" target=\"_blank\">Variant Effect Scoring Tool </a>","1")
			),
			"Disease Related"=> array ( 
				"cosmic70"=>array ("any","<a href=\"http://www.sanger.ac.uk/perl/genetics/CGP/cosmic/\" target=\"_blank\" >COSMIC v70</a>","0"),
				"omimGene"=> array ("any","<a href=\"http://www.ncbi.nlm.nih.gov/omim\" target=\"_blank\" >OMIM</a> (gene-centric)","0"),
				"clinvar_20140702"=> array ("any","<a href=\"http://www.ncbi.nlm.nih.gov/clinvar/\" target=\"_blank\" >ClinVar (July 2014)</a> ","0")
			),
			"Non-coding Regulators"=>array(
				"wgRna"=>array("nc","<a href=\"http://hgdownload.soe.ucsc.edu/goldenPath/hg19/database/\" title=\"UCSC miRNA annotation\" target=\"_blank\" >snoRNA and miRNA annotations</a>","0"),
				"HMDD"=>array ("nc","<a href=\"http://202.38.126.151/hmdd/mirna/md/\" target=\"_blank\" title=\"Annotations including scores of miRNAs and its role in human disease\">HMDD Full Annotations</a>",""),
				"lincRNA"=>array("nc","<a href=\"http://www.ncbi.nlm.nih.gov/pubmed?term=21890647\" target=\"_blank\" title=\"Long Intergenic non-coding RNAs\">Linc RNA </a>","0"),
				"lncipedia"=>array("nc","<a href=\"http://lncipedia.org\" title=\"Long non-coding miRNAs\" target=\"_blank\" >Lncipedia</a>",""),
				"SomamiR"=>array ("nc","<a href=\"http://http://compbio.uthsc.edu/SomamiR/\" title=\"microRNA targets in Cancer pathways\" target=\"_blank\" >SomamiR</a>",""),
				"VISTA_enhancer"=>array("nc","<a href=\"http://enhancer.lbl.gov/frnt_page_n.shtml\" title=\"Experimentally validated enhancers\" target=\"_blank\" >VISTA Enhancers</a>","0"),
				"wgEncodeAwgTfbsUniform"=>array("nc","<a href=\"http://genome.ucsc.edu/cgi-bin/hgTrackUi?db=hg19&g=wgEncodeAwgTfbsUniform\" title=\"Encode Transcription Factor ChIP-seq\" target=\"_blank\" >ENCODE ChIP Seq Uniform Peaks</a>","0"),
				"wgEncodeMethylRrbs"=>array("nc","<a href=\"http://genome.ucsc.edu/cgi-bin/hgFileUi?db=hg19&g=wgEncodeHaibMethylRrbs\" title=\"Encode Methylation\" target=\"_blank\" >ENCODE Methylation by RRBS</a>","0"),
				"cpgIslandExt"=>array("nc","<a href=\"http://genome.ucsc.edu/cgi-bin/hgTrackUi?&c=chrX&g=cpgIslandExt\" target=\"_blank\" >CpG Islands</a>","0"),
				// "FunSeq"=>array("nc","<a href=\"http://funseq.gersteinlab.org/\"><b>Fun</b>ction based Prioritization of <b>Seq</b>uence Variants (FunSeq)</a>","0"),
			),
			"Targets of Non-coding Regulators"=> array (
				"tfbsConsSites"=>array("nc","Conserved Transcription Binding Sites","0"),
				"targetScanS"=>array("any","<a href=\"www.targetscan.org/\" title=\"miRNA targets using TargetScanS\" target=\"_blank\" >miRNA targets</a>","0"),
				"microPIR_targets"=>array("nc","<a href=\"http://www4a.biotec.or.th/micropir\" title=\"microRNAs targeting promoter regions\" target=\"_blank\" >microPIR targets</a>",""),
				"SomamiR_targets"=>array("any","<a href=\"http://http://compbio.uthsc.edu/SomamiR/\" title=\"microRNA targets in Cancer pathways\" target=\"_blank\" >SomamiR targets</a>",""),
				"VISTA_enhancer_expr_data"=>array("any","<a href=\"http://enhancer.lbl.gov/frnt_page_n.shtml\" title=\"Experimentally validated enhancer expression data\" target=\"_blank\" >VISTA Expression Targets</a>","0")
			),
			"Known Variations"=>array(
				"snp138"=>array ("any","<a href=\"http://www.ncbi.nlm.nih.gov/projects/SNP/\" target=\"_blank\">dbSNP (build 138)</a> ",'0'),
			), 
			"Genomics Datasets"=>array(
				"cg69"=>array("any","<a href=\"http://www.completegenomics.com/public-data/69-Genomes\" target=\"_blank\" >Complete Genomics Genomes</a>","0"),
				"1000gALL_sites_2014_09"=>array("any","<a href=\"http://www.1000genomes.org\" target=\"_blank\" >1000 Genomes Project</a> <em> updated Sept 2014</em>",0),
				"hapmap_3_3"=>array("any","<a href=\"http://hapmap.ncbi.nlm.nih.gov\" target=\"_blank\" >HapMap project</a>",0),
				"gwasCatalog"=>array("any","<a href=\"http://www.genome.gov/gwastudies/\" target=\"_blank\" >GWAS catalog</a>",0),
				 "nci60"=>array("any","<a href=\"http://cancerres.aacrjournals.org/content/73/14/4372.full\" target=\"_blank\" > NCI-60</a>","0"),
				 "ESP6500si_all"=>array("any","<a href=\"http://evs.gs.washington.edu/EVS/\" target=\"_blank\" >NHLBI-Exome Sequencing Project (ESP)</a>","0")
			),
			"Genomic Features"=>array(
				"nonB"=>array("any","<a href=\"http://nonb.abcc.ncifcrf.gov/\" title=\"DNAs that do not fall into a right-handed Watson-Crick double-helix \" target=\"_blank\" >NonB</a>","0"),
				"genomicSuperDups"=>array("any","Segmental duplications from UCSC","0"),
				"prosite_domains"=>array("any", "<a href=\"http://prosite.expasy.org/\" target=\"_blank\" >Predicted Prosite Domains</a>","0"),
				"dgvMerged"=>array("any","<a href=\"http://dgv.tcag.ca/dgv/app/home\" title=\"Database of Genomic Variants\" target=\"_blank\" >Database of Genomic Variants (DGV)</a>","0"),
				"rptMask"=>array("any","Repeat Masker","0"),
				'cytoBand'=>array("any","CytoBand","0"),
				"Fantom5_CAGE_peak"=>array("any","<a href=\"http://fantom.gsc.riken.jp/\" target=\"_blank\" >FANTOM5</a> CAGE peaks",""),
				"Fantom5_TSSpredictions"=>array("any","<a href=\"http://fantom.gsc.riken.jp/\" target=\"_blank\" >FANTOM5</a> Enhancers",""),
				"Fantom5_enhancers"=>array("any","<a href=\"http://fantom.gsc.riken.jp/\" target=\"_blank\" >FANTOM5</a> CAGE peaks",""),
				"PTM"=>array("any","Post-Translational Modifications from <a href=\"http://www.phosphosite.org/\" target=\"_blank\" >PhosphoSite</a> and <a href=\"http://www.phosida.com/\" target=\"_blank\" >Phosida</a>","0")
			),
			"Alternative Splicing and Enhancers"=>array(
				"alt_splice"=>array("any","<a href=\"http://www.ensembl.org/info/docs/genebuild/ase_annotation.html\" target=\"_blank\" >Ensembl63 Splice Events</a>",""),
 				"ESEFinder"=>array("any","<a href=\"http://rulai.cshl.edu/cgi-bin/tools/ESE3/esefinder.cgi?process=home\" title=\"Scores of each exonic on 6 SR proteins using matrices found on ESEFinder website\" target=\"_blank\" >ESE Finder </a>",""),
				"tassdb"=>array("any","<a href=\"www.tassdb.info/\" target=\"_blank\" >Tandem Splice Database</a>","0")
			),
			"Sequence Mapability and Mutability"=>array(
				"wgEncodeCrgMapabilityAlign100mer"=>array("any","<a href=\"http://genome.ucsc.edu/cgi-bin/hgFileUi?db=hg19&g=wgEncodeMapability\" target=\"_blank\" >Encode's Mapability Factor (100mer)</a>",""),
				"wgEncodeDukeMapabilityUniqueness35bp"=>array("any","<a href=\"http://genome.ucsc.edu/cgi-bin/hgFileUi?db=hg19&g=wgEncodeMapability\" target=\"_blank\" >Uniqueness Factor  (35bp)</a>",""),
				"wgEncodeDukeMapabilityRegionsExcludable"=>array("any","<a href=\"http://genome.ucsc.edu/cgi-bin/hgFileUi?db=hg19&g=wgEncodeMapability\" target=\"_blank\" >Excludable Regions</a>",""),
			),
			"Pathway Visualization"=>array(
				"pathview"=>array("any","<a href=\"http://bioconductor.org/packages/release/bioc/html/pathview.html\" target=\"_blank\" >Pathview</a>","")
			),
	);
	echo "<tr><td align=\"center\"><hr /></td></tr>";
	echo "<tr><td >Customize your annotation below:</td></tr>";
	foreach ($db_arr as $category=>$db) {
		
		$addon= " style=\"display:none\">" ;
		$imgtype="expand.gif";
		echo "<tr>
			<td colspan=\"4\">
			<table width=\"600px\" >
				<tr >
				<td><a title=\"Click here to view all $category databases available\" href=\"javascript:showOrHide('$category','')\"><img id=\"img_$category\" width=\"15\" height=\"15\" src=\"/images/$imgtype \"  />&nbsp;$category</a></td>
				<td align=\"right\"><a href=\"javascript:showOrHide('$category',true)\"  title=\"Click here to view all databases available for $category\" >Select all in $category <input type=\"checkbox\" id=\"allmy$category\" onclick=\"checkAllCat(this,this.id)\" title=\"Check this box to select all $category databases\"/></a></td>
				
			</tr></table>
			</td></tr>";
		echo "<tr  class=\"category\" id=\"$category\" $addon";
		
		echo "	<td colspan=\"4\">";
		
		echo "<fieldset><table width=\"600px\" >";
		echo "<tr>";
		foreach ($db_arr[$category] as $db=>$info){
				$related="report_annotdb_$db";$other="report_circos_type_$db";$class="allmy$category";
				if (preg_match("/(Protein|Known)/",$category) && !preg_match("/runIndelNMD/",$db)){
					$checked="checked=\"true\" ";
				}elseif (preg_match("/(runIndelNMD|pathview)/",$db)){
					$related="report_$db"; $other="";
					$checked=" ";$class="$category";
				}else{
					$checked='';
					
				}
				echo "
				<tr>
					<td>&nbsp;</td>
					<td width=\"50%\">";
					echo "$info[1]";
				if (!preg_match("/pathview/",$db)){
					echo "</td>
					<td><input type=\"checkbox\" class=\"$class\" name=\"$related\" id=\"$related\" $checked onClick=\"if(!this.checked){checkRelated('$other','$related')}\" /> Annotation </td>";
				}else{
					echo "</td><td colspan=\"2\"><input type=\"checkbox\" name=\"user_run$db\" id=\"user_run$db\"  /> KEGG Network Graphs</td>";
				}
					echo "<td></td>";
					echo "</tr>";
		}
		echo "</table></fieldset>
		</td></tr>";		
	}
	
?>
<tr>	
<td><colspan="4"><hr /></td></tr>
<tr>
	<td colspan="4">
		<b>Specify your own annotation databases:</b>
	</td>
</tr>
<tr>	
	<td colspan="4">
		<button type="button" onClick="addDB('viz_databases')" >Add User-defined Annotation File</button> (<a title="What are acceptable uploadable file types?" target="_blank" href="/apps/site/sub#ia_databases" >?</a>) 
	</td>
</tr>
<tr><input type="hidden" id="user_counter" value=0 name="user_counter" />
	<td colspan="3" id="viz_databases" style="display:none">
	<font color="red"><i>There is a maximum upload size of 100M for all uploaded files (including your input file).  Please <a href="submit_a_question">contact</a> us if you have larger files you wish to use.</i></font><br />
	</td>
</tr>
<tr><td colspan="4"></td></tr>
<tr><td colspan="4"><a href="javascript:showOrHide('_general_opts','')"><img id="img_general_opts" width="15" height="15" src="images/expand.gif"/><b><font color="#006DB5">General Options:</font> </a></b></td></tr>
<tr id='_general_opts' style="display:none"><td><table width="500"><tr>
		<td colspan="4">
			<input type="checkbox" name="report_annotdb_FlankingSequence" value="on" /> Include 20bp flanking sequence around mutation in report?  
		</td>
</tr>
	<tr> <td colspan="4"><input type="checkbox" name="user_name_column_header" value="1" />&nbsp;Add your filename to the leftmost column of your output file?</td></tr>
	<tr> <td colspan="4"><input type="checkbox" name="user_splitZyg" value="1" />&nbsp;Add zygosity as separate column (1=homozygous, 0=heterozygous) for single patient VCF</td></tr>

	<tr> <td colspan="4"><input type="checkbox" name="user_writeVCF" value="1" />&nbsp;Convert final output back to VCF file with Annotations in INFO column (only if original file is in VCF format)</td></tr>
	<!-- <tr> <td colspan="4"><input type="checkbox" name="user_calcAF" value="true" />&nbsp;Parse allele frequency and depth from your VCF file (Info column)?</td></tr> -->
	
</table>
</table>
