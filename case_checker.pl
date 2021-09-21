#!/usr/bin/perl
=pod
    Copyright (C) 2015, 2016 Tim O'Connell

    This file is part of Bloodhound.

    Bloodhound is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Bloodhound is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Bloodhound.  If not, see <http://www.gnu.org/licenses/>.
=cut
# Script called by cron to check PACS for new studies that a person is tracking

# -------------------------------------------------------
# Variables, etc
# -------------------------------------------------------
use Data::Dumper; # just for debugging
$Data::Dumper::Sortkeys = 1;  #also for debugging
use POSIX qw(strftime); # Used only for getting the proper time for logfile
use DBI; # use for connectivity with MySQL
use LWP::Simple;
$ENV{DCMDICTPATH}='/usr/share/libdcmtk2/private.dic:/usr/share/libdcmtk2/dicom.dic:';

# DB Variables
my $database_host="localhost"; # Host (name or IP) of MySQL Server
my $database_tcp_port="3306"; # TCP port of MySQL server
my $database_username="bloodhound_user"; # MySQL username
my $database_password="bloodhound_pw"; # MySQL password
my $database_name="bloodhound"; # Database name
my $error_message = "Database error; please contact the site administrator.";
my $dsn = "DBI:mysql:database=$database_name;host=$database_host;port=$database_tcp_port";

# PACS variables - edit these for your own PACS
my $pacsaetitle = “PACSAETITLE”;
my $pacsipaddress = “x.x.x.x”;
my $pacstcpport = “xxx”;
my $myaetitle = “MYAETITLE”;

# Time variables
my $now = time();
my $mysql_dttm = POSIX::strftime('%Y-%m-%d %H:%M:%S', localtime($now));
my $before1 = $now - (60*60*24);      # 1 day ago
my $dicomdate_now = POSIX::strftime( '%Y%m%d', localtime($now) );
my $dicomdate_yesterday = POSIX::strftime( '%Y%m%d', localtime($before1) );

# Other
my $infile = $ARGV[0];
my ($institution);
my (@findscu_dump, 
    @studies_to_check, 
    @ready_studies,
);

my (%studies_to_check,
    %studies, 
    %new_studies,
	%emails_to_send,
);

# Logging setup - edit these to be relevant to your path
my $log_file_directory = "/var/www/html/bloodhound/log/";
my $logfile = "$log_file_directory/bloodhound_update.log";
my $error_log = "$log_file_directory/bloodhound_update_error.log";
open(STDERR, ">>", $error_log);
open LOGFILE, ">>$logfile";
print LOGFILE "*******************************************************************************\n";
print LOGFILE "[" . localtime . "]: Beginning search for new studies\n";
print LOGFILE "*******************************************************************************\n";

# -------------------------------------------------------
# main()
# -------------------------------------------------------

connect_to_db();
find_studies_to_check();
find_studies_yesterday();
add_new_studies_to_db();
send_update_emails();

# -------------------------------------------------------
# subroutines
# -------------------------------------------------------
sub connect_to_db() {
    print "Connecting to DB\n";
    $dbh = DBI->connect($dsn, $database_username, $database_password, {'RaiseError' => 1}) or die $DBI::errstr;
}

sub find_studies_to_check() {
    $count = 0;

    print "Finding studies to check\n";
    # Need to validate the SQL below to make sure it is catching all of the cases
	#    my $sth = $dbh->prepare("SELECT p.idstudies, p.mrn, p.accession, p.study_date, p.study_time, p.idusers_fk, t.modality, u.email FROM primary_studies p INNER JOIN tracked_modalities t on p.idstudies=t.idstudies_fk JOIN users u on p.idusers_fk=u.idusers WHERE p.status='active'");
    my $sth = $dbh->prepare("SELECT p.idstudies, p.mrn, p.accession, p.study_date, p.study_time, p.idusers_fk, p.email_status, t.modality, b.bodypart, u.email, u.username FROM primary_studies p INNER JOIN tracked_modalities t on p.idstudies=t.idstudies_fk LEFT OUTER JOIN tracked_bodyparts b on p.idstudies=b.idstudies_fk JOIN users u on p.idusers_fk=u.idusers WHERE p.status='active'");
    $sth->execute();
    while (my $ref = $sth->fetchrow_hashref()) {
#        print "Found a row: id = $ref->{'idstudies'}, mrn = $ref->{'mrn'}, tracked_mod = $ref->{'modality'}\n"; #DEBUG
        $studies_to_check{$count}{'idstudies'} = $ref->{'idstudies'};
        $studies_to_check{$count}{'idusers_fk'} = $ref->{'idusers_fk'};
        $studies_to_check{$count}{'mrn'} = $ref->{'mrn'};
        $studies_to_check{$count}{'modality'} = $ref->{'modality'};
        $studies_to_check{$count}{'bodypart'} = $ref->{'bodypart'};
        $ref->{'study_date'} =~ s/\-//g;
        $studies_to_check{$count}{'study_date'} = $ref->{'study_date'};
        $ref->{'study_time'} =~ s/\://g;
        $studies_to_check{$count}{'study_time'} = $ref->{'study_time'};
        $studies_to_check{$count}{'accession'} = $ref->{'accession'};
        $studies_to_check{$count}{'email'} = $ref->{'email'};
        $studies_to_check{$count}{'username'} = $ref->{'username'};
        $studies_to_check{$count}{'email_status'} = $ref->{'email_status'};
        $count++;        
    }
    $sth->finish();
    print Dumper(%studies_to_check); #DEBUG
}

sub find_studies_yesterday() {
	my $query;
    my $count = 0;
    my $count2 = 0;
    my @findscu_tempdump;
	my @findscu_subdump;

    foreach my $key (keys %studies_to_check) {
        # I may need to have an if/then point here depending on the type of tracking - e.g. body part +/- modality
    	$query = "findscu -aet $myaetitle -aec $pacsaetitle -S -k 0008,0052=\"STUDY\" -k 0008,0020=\"$dicomdate_yesterday\" -k 0010,0020=\"" . $studies_to_check{$key}{'mrn'} . "\" -k 0008,0061=\"" . $studies_to_check{$key}{'modality'} . "\" -k 0008,0050 -k 0008,0080 -k 0008,1030 -k 0010,0010 -k 0008,0030 $pacsipaddress $pacstcpport 2>&1";
	    print $query . "\n"; # DEBUG
    #	print LOGFILE "[" . localtime . "]: $query\n";
        @findscu_dump = `$query`;
        if (@findscu_dump) {
        	foreach (@findscu_dump) { print $_; }			
            foreach (@findscu_dump) {
                if ($_ =~ /^W\: Find Response/) {
                    # Each DICOM C-FIND response has a separate response number, and it's possible a patient
                    # has more than one new study on the day being checked
                    $count++;
                }
                elsif ($_ =~ /\((.+)\).+\[(.+)\]/) {
                    $_ =~ m/\((.+)\).+\[(.+)\]/;
                    $dicom_key = "g$1";
                    $result = $2;
                    $dicom_key =~ s/\,/e/;
                    $result =~ s/\s+$//;
#                    print "DICOM KEY: $dicom_key  RESULT: $result\n";
                    $studies{$count}{$dicom_key} = $result;
                }
                elsif ($_ =~ /\((.+)\).+\((.+)\)/) {
                    $_ =~ m/\((.+)\).+\((.+)\)/;
                    $dicom_key = "g$1";
                    $result = $2;
                    $dicom_key =~ s/\,/e/;
                    $result =~ s/\s+$//;
                    $studies{$count}{$dicom_key} = $result;
                }
            }
            undef(@findscu_dump);
            print "PRINTING ORIGINAL STUDIES HASH\n"; # DEBUG
            print Dumper(%studies); # DEBUG

            # Now, step through the %studies hash as the patient may have multiple new studies the next day
            foreach my $newkey (keys %studies) {
                if ($studies{$newkey}{'g0008e0050'} != $studies_to_check{$key}{'accession'}) { 
                    # If the new study isn't the same as the original (for checking the study the day after it's entered)
                    if ($studies{$newkey}{'g0008e0020'} > $studies_to_check{$key}{'study_date'}) {
                        # If the study date is newer (the most often case)
                        # then copy the hash
                        print "Copying the hash\n";
                        $new_studies{$count2}{'g0008e0020'} = $studies{$newkey}{'g0008e0020'}; 
                        $new_studies{$count2}{'g0008e0030'} = $studies{$newkey}{'g0008e0030'}; 
                        $new_studies{$count2}{'g0008e0050'} = $studies{$newkey}{'g0008e0050'}; 
                        $new_studies{$count2}{'g0008e0061'} = $studies{$newkey}{'g0008e0061'}; 
                        $new_studies{$count2}{'g0008e0080'} = $studies{$newkey}{'g0008e0080'}; 
                        $new_studies{$count2}{'g0008e1030'} = $studies{$newkey}{'g0008e1030'}; 
                        $new_studies{$count2}{'g0010e0010'} = $studies{$newkey}{'g0010e0010'}; 
                        $new_studies{$count2}{'g0010e0020'} = $studies{$newkey}{'g0010e0020'}; 
                        $new_studies{$count2}{'idstudies'} = $studies_to_check{$key}{'idstudies'}; 
                        $new_studies{$count2}{'idusers_fk'} = $studies_to_check{$key}{'idusers_fk'}; 
                        $new_studies{$count2}{'email'} = $studies_to_check{$key}{'email'}; 
                        $new_studies{$count2}{'username'} = $studies_to_check{$key}{'username'};
                        $new_studies{$count2}{'email_status'} = $studies_to_check{$key}{'email_status'}; 
                    }
                    elsif ($studies{$newkey}{'g0008e0020'} == $studies_to_check{$key}{'study_date'}) {
                        if ($studies{$newkey}{'g0008e0030'} > $studies_to_check{$key}{'study_time'}) {
                            # This case matches when the patient has a newer study later the same day as the original
                            # then copy the hash
                            print "Copying the hash\n"; # DEBUG
                            $new_studies{$count2}{'g0008e0020'} = $studies{$newkey}{'g0008e0020'}; 
                            $new_studies{$count2}{'g0008e0030'} = $studies{$newkey}{'g0008e0030'}; 
                            $new_studies{$count2}{'g0008e0050'} = $studies{$newkey}{'g0008e0050'}; 
                            $new_studies{$count2}{'g0008e0061'} = $studies{$newkey}{'g0008e0061'}; 
                            $new_studies{$count2}{'g0008e0080'} = $studies{$newkey}{'g0008e0080'}; 
                            $new_studies{$count2}{'g0008e1030'} = $studies{$newkey}{'g0008e1030'}; 
                            $new_studies{$count2}{'g0010e0010'} = $studies{$newkey}{'g0010e0010'}; 
                            $new_studies{$count2}{'g0010e0020'} = $studies{$newkey}{'g0010e0020'}; 
                            $new_studies{$count2}{'idstudies'} = $studies_to_check{$key}{'idstudies'}; 
                            $new_studies{$count2}{'idusers_fk'} = $studies_to_check{$key}{'idusers_fk'}; 
                            $new_studies{$count2}{'email'} = $studies_to_check{$key}{'email'}; 
                            $new_studies{$count2}{'username'} = $studies_to_check{$key}{'username'}; 
                            $new_studies{$count2}{'email_status'} = $studies_to_check{$key}{'email_status'};
                        }
                    }
                $count2++;
                }
            }
        undef (%studies);
        }
    }
    print "PRINTING NEW/COPIED STUDIES HASH\n"; # DEBUG
    print Dumper(%new_studies); # DEBUG
}

sub add_new_studies_to_db() {
    my $sql;

    if (%new_studies) {
        foreach my $key (sort keys %new_studies) {
            # This needs to be updated with an UPSERT style command to prevent it from re-inserting in case it gets run more than once

            $sql = "INSERT INTO new_studies (study_date, study_time, accession, modality, institution, exam_description, patient_name, mrn, idstudies_fk, idusers_fk, status, created_dttm) VALUES ('$new_studies{$key}{'g0008e0020'}', '$new_studies{$key}{'g0008e0030'}', '$new_studies{$key}{'g0008e0050'}', '$new_studies{$key}{'g0008e0061'}', '$new_studies{$key}{'g0008e0080'}', '$new_studies{$key}{'g0008e1030'}', '$new_studies{$key}{'g0010e0010'}', '$new_studies{$key}{'g0010e0020'}', '$new_studies{$key}{'idstudies'}', '$new_studies{$key}{'idusers_fk'}', 'new', '$mysql_dttm')";
            print $sql . "\n";
            my $sth = $dbh->prepare($sql);
            $sth->execute();            
            $sth->finish();

#            foreach my $subkey (keys $new_studies{$key}) {
#               print "KEY: $key    SUBKEY: $subkey    VALUE: $new_studies{$key}{$subkey}\n"; # DEBUG
#            }

            # Now push the user's email to the @emails_to_send hash 
            # but use grep to make sure that we're just adding the email once, even if the user has multiple new studies to follow up on
			# previously I was using an array but as I need to pass a bunch of info to the user, I'm going to
			# just overwrite the hash root key of the email address so that we don't email the user a bunch of times
			# I don't want to have the email send the user to the new_studies.php page, just to their my_studies.php
			# page
			if ($new_studies{$key}{'email_status'} eq "send") {
				$emails_to_send{$new_studies{$key}{'username'}} = $new_studies{$key}{'email'};
			}
			# push @emails_to_send, $new_studies{$key}{'email'} unless grep{$_ eq $new_studies{$key}{'email'}} @emails_to_send;
        }
    }
}

sub send_update_emails() {
    # This function looks at the %emails_to_send hash and tries to send everyone an email 
    # that they have a tracked study with a new follow up
	# to get outside of the hospital firewall, what I've done is create a gmail user called XXX@gmail.com
    # that will be sending the emails. 
	# I've then created a script.google.com script that belongs to this user and is publicly accessible that 
	# can take some variables passed to the script and send a gmail message to the intended user of bloodhound
	# to let them know they have a new study to view.

    print "Emails of people to send to are:\n"; # DEBUG
    print Dumper(%emails_to_send); # DEBUG
    
    foreach my $key (sort keys %emails_to_send) {
        # Now send the user an email about the new study
        print "SENDING EMAIL to USER $key at email address " . $emails_to_send{$key} . "\n"; #DEBUG
		# And remember the single quotes otherwise perl will interpolate the email address
		my $url = 'https://script.google.com/PATH_TO_YOUR_URL_HERE?username=' . $key . '&recipient=' . $emails_to_send{$key};
		
		# And we'll use LWP to call the webpage
		my $result = get $url;
		
		if ($result =~ /Success/) {
			# Then the email was sent
			print LOGFILE "Email successfully sent to user $key about new studies\n";
		}
		else {
			print STDERR "Email failed sending to user $key with URL: $url\n";
		}
		
        undef $url;
		undef $result;
    }
}
