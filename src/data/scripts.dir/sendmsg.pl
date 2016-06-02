#!/usr/bin/perl
=head v2.0
This version is uses the fact the website accepts gzip, bzip, tar, tgz file formats for upload
So we must sanitize the inputs before moving to our work directory  (for ia_upload_only)
No longer accepts lmt inputs for checks!

v3.0
addition of viz options in the mix
=cut
use strict;
use warnings;
use FindBin qw($RealBin);
use lib $RealBin;
use lib "$FindBin::Bin/perl_modules";
use lib "/code/src/data/scripts.dir/perl_modules/MIME-Lite-3.030/lib";
use Mail::Sendmail;
use MIME::Lite;

###################### APPLICATION SPECIFIC - to be changed #########################3
my $ADMIN="vuonghm\@mail.nih.gov";
my $application_email='NCI-Frederick AVIA@mail.nih.gov';
my $weburl='http://fr-s-bsg-avia-t:8000';
my $webserver='fr-s-bsg-avia-t';
my $application_name="AVIA-lite";

####################### Other Variables ###################3
my $id=$ARGV[0];#e.g mirna504e2e5e6e5f8.abcc.init
my $bin=`dirname $0`;chomp $bin;
my $input_abcc="$bin/../completed/$id";#e.g /users/abcc/vuonghm/scripts.dir/avia/public/complete/mirna504e2e5e6e5f8.abcc.init
if ($id=~/^\//){
	$input_abcc=$id;
}
$id=~s/\.abcc(.init){0,1}//;
if ($id){
	$id=`basename $id`;chomp $id;
}
# print "$input_abcc\n";
my $msg;my $email;my $subj;
if ($#ARGV>=1){
	# print __LINE__."\n";
	if ($ARGV[1]==1){
		if ($id=~/mirna/i){
			$subj="$application_name completed $id";
			$msg="Thank you for using $application_name.  Your id ($id) has completed processing.  Please visit our website retrieval service to get your data:\n$weburl/results.php?id=$id\n";
		}elsif ($id=~/setup/i){
			$msg="Thank you for using $application_name.  In order complete your setup request, you need to click on the following link to confirm your setup configuration request:\n$weburl/setup.php?id=$id";			
			$subj="$application_name request confirmation";
		}elsif ($id=~/gene/i){
			$subj="$application_name completed $id";
			$msg="Thank you for using $application_name.  Your id <font color=\"red\">$id</font> has completed processing.  <br />Please visit our website retrieval service to get your data:\n$weburl/results.php?id=$id\n";
		}
		# print "looking for user.email in $input_abcc\n";
		my $email=`grep 'user.email' $input_abcc`;chomp $email;
		$email=~s/user\.email=//g;
		sendMsg("$subj",$msg,$email);
	}elsif ($#ARGV>=2){
		$email=$ARGV[0];
		$subj=$ARGV[1];
		$msg=$ARGV[2];
		sendMsg("$subj",$msg,$email);		
	}else{
		$msg="$application_name $id has errored.  Please check $id in $bin\n";
		$email=$ADMIN;
		sendMsg("$application_name could not completed request:$id",$msg,$email);
	}
}
sub sendMsg{#new with attachment
	my $subject=shift;
	my $text=shift;
	my $to=shift;
  	my $path_to_image = qq(/code/src/annovar/parseData/avia_logo_01.png);
	my $message = MIME::Lite->new(
    From    => "$application_email",
    To      => $to,
    Bcc      => $ADMIN,
    Subject => $subject,
    Type    => 'multipart/related',
);
	$msg="<h6>Please do not reply to this message. It goes to an unattended inbox.  To contact us, please use our webform at <a href=\"http://avia.abcc.ncifcrf.gov/apps/site/submit_a_question\">http://avia.abcc.ncifcrf.gov/apps/site/submit_a_question</a><br />";

# Now, we have to attach the message in HTML. First the HTML
my $html_message = qq(
	<body>
     
     <img src="cid:avia_logo.png">
     <br />
     $text
     $msg
     </body>
);

# Now define the attachment
$message->attach (
    Type => 'text/html',
    Data => $html_message,
);

# Let's not forget to attach the image too!
$message->attach (
    Type => 'image/png',
    Id   => 'avia_logo.png',
    Path => "$path_to_image",
);

$message->send
    or die qq(Message wasn't sent: $!\n);
print "Sent!";
}
