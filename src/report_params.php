<table>
<tr><th colspan="4" align="left"><font color="#0000FF"><b> Parameters for Reporting</b></font></th></tr>
		<tr><td colspan="4"><input type="checkbox" name="report_runAggregation" value="true" checked="true" /> Run Impact Analysis </td></tr>
		<tr><td><font color="#800517">Select output type: </font></td>
			<td align="left">
				<select name="report_outputtype"> 
					<option value="donordir"> By Donor and Dir </option>
					<option value="donor"> By Donor</option>
					<option value="exon"> By Exon </option> 
					<option selected="true" value="position"> By position </option>
					</select>
				</td><td></td></tr>
		<tr><td><font color="#800517">Min. No. of Reads to report variation:</font></td>
			<td align="center">
				<input type="txt" name="report_include_threshold" value="2"/> 
			</td></tr>
		<tr><td><font color="#800517">Do not call N number of bases from end of read:</font></td>
			<td align="center">
				<input type="txt" name="report_donotcallends" value="10"/> 
			</td></tr>
		<tr><td><font color="#800517">Report variants on only one strand:</font></td>
			<td align="center">
				<input type="radio" name="report_requireDS" value="0" checked="yes" /> yes
				<input type="radio" name="report_requireDS" value="1" /> no
			</td><td></td></tr>
		<tr><td><font color="#800517">Generate PDF chromat files:</font></td>
			<td align="center">
				<input type="radio" name="report_runViewer" value="yes" checked="yes" /> yes
				<input type="radio" name="report_runViewer" value="no" /> no
			</td><td><em>This can be generated later</em></td></tr>
		
</table>