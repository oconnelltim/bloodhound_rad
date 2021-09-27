# bloodhound_rad
Web-based case tracker for radiology departments

## Introduction
For as long as radiologists have been looking at studies, for weeks and months later, they have wondered..."whatever happened to that patient?"  But amidst the business of our days, few of us have the time to maintain a 'lookup-list' - a list of patients with interesting findings that we want to see the imaging evolution of their case.

This is the problem that bloodhound tries to solve.  When set up properly and customized for your insitution, it can be a minimal-click method of adding cases to a web-based registry of *your* interesting cases, which will track that patient going forward and notify you when the patient has had a follow-up scan.

Bloodhound has been written in PHP; it is _pretty_ easy to set up inside of a radiology department as long as you can set up a webserver inside your department with access to the web, and configure [dcmtk](https://dicom.offis.de/dcmtk.php.en), and work with your local PACS administrator to set up C-FIND access for the webserver. Thus you'll need a bit of technical ability, and you'll want to do some customization of the scripts to suit your particular institution's needs.  But, it will be fun and you'll learn something, and hopefully you'll become a better radiologist and help patients along the way. 

## Requirements

* A webserver inside your department running Apache/PHP/MySQL
* [dcmtk](https://dicom.offis.de/dcmtk.php.en), set up on the webserver, with the executable for `findscu` copied into the directory of the webserver (insecure), or accessible via the path of the webserver user
  * dcmtk's `findscu` utility has to be set up with your PACS admin to be able to perform `C-FIND` queries on your webserver; this usually requires your PACS admin to add the webserver's IP address, TCP port, and an AETITLE for it into the PACS admin interface, and may require the help of a network admin if your webserver is firewalled from your PACS server
  * In order for the webserver to send your users emails about new studies (if you want that functionality), the webserver needs to be able to access the Internet/WWW - this means port 443 access either directly to the Internet or via your institution's web proxy server(s)
* The ability to add new 'buttons' to your PACS interface, that can open web pages and pass along in-context case information, in this case, an accession number of the case that you're looking at, and your PACS username, via an HTTP `GET` request (most, if not all PACS vendors support this e.g. Agfa Impax, GE Centricity, Philips IntelliSpace, etc).  Your PACS admin might know how to do this, and if not, then your vendor will. 
* PERL installed on the webserver, with the DBI and DBD::mysql modules installed

## How to Use Bloodhound 

The workflow is supposed to be pretty straightfoward:

1. You are looking at a case with an interesting finding that you'd like to find out what happens on the patient's next scan.
2. You click a custom-made button that opens the bloodhound webpage `create_case.php`
3. You add the case details and modality that you want to track
4. Later, the patient gets another scan; bloodhound does a nightly query of cases being tracked, and finds that the patient has had another scan. Bloodhounds sends you an email notifying you that a patient you are tracking has had another scan; you can click on the link when you're back inside the work firewall to view the case. 
5. You can later log into bloodhound to stop tracking cases, edit cases, etc.

## How it Works / How to Set it Up

To set up bloodhound for the first time, read the very brief `INSTALL` file in this repository.  Then, install `dcmtk` on your PHP-enabled webserver.  Copy all the bloodhound files into a directory on your webserver that you wish to copy files from.  Create a database in MySQL that bloodhound will use.  Use the `bloodhound.sql` file to create the tables in the database.  Ensure that your webserver can execute the `findscu` (linux) or `findscu.exe` (windows) file essentially from the command-line, either by putting it in the same directory (insecure) or via the web-server user's path.  

Create an `admin` user in the database by logging into the MySQL command line and executing a command like:

`INSERT INTO users (username, password, full_name, user_type) VALUES ('admin', 'e55ab2f362b888c682102c9d42c67cfb', 'admin', 'admin');`

(Here, the password is the MD5 hash of `radiology`). 

Log into the Bloodhound UI as the `admin` user, using the cleartext password that you MD5 hashed above, and create a new user with your PACS username (e.g. `juliesmith`). 

Next, create a button in your PACS profile that links to your bloodhound server and can pass it your PACS username along with the Accession # of the case that you're looking at, with an HTTP GET string like:

`http://bloodhound/create_case.php?accession=12345678&username=juliesmith`

When you click on this button, it should load the bloodhound `create_case.php` page; this page should execute `findscu` and perform a DICOM `C-FIND` query on the case that you were looking at, and retrieve a bunch of patient metadata.  It will allow you to edit the case parameters, and then 'create the case', and store it in the MySQL database you created earlier. 

You should then check to ensure that your PERL environment is set up and that the `case_checker.pl` file can run nightly (either via `cron` in linux or whatever Windows uses to automate execution of files).  This script will use `findscu` again to check PACS for new cases that match the follow-up criteria that you've set up for the patient that you are tracking. 

Finally, you should create a gmail account for bloodhound at your institution.  You can use this account to create a [Google Apps script](https://www.google.com/script/start/) which will send your users emails (that just contain a link to the bloodhound case they can only open when inside your firewall, and no PHI).  The `case_checker.pl` script will call this Google Apps Script if the webserver has HTTPS access to the Internet to notify your users nightly when their patient have had follow-up studies. 

## A Few Important Notes & a Warning

It is important that you run all of this by your local department chair and PACS team and everyone else involved to make sure you're following your institution's security protocols, etc.  A certain road to somewhere is paved with good intentions, and you don't want to cause more problems than the good you're trying to do. 

WARNING: if there are bugs in this software or you create bugs in this software by not configuring it properly, you could issue unconstrained PACS C-FIND queries.  This is bad and can really tie up your PACS server for a very long time, potentially denying service to users who are relying on it to provide critical patient care. So be aware that this is a risk, and work with your PACS admins to ensure this won't happen.  This software is provided 'as-is', with no warranty whatsoever, and any harm you cause is your fault entirely.  It is provided as 'sample software', for you to customize to your institution's needs, not as a fully-featured, functioning system. 

You will also need to curate the Bloodhound user database, so that users aren't getting emails long after they've left the institution, and that the list of nightly queries against your PACS server doesn't grow to infinity and create a denial-of-service attack against your PACS server when the `case_checker.pl` case is running.  All in all, this is dangerous software if not configured correctly/properly by someone who knows what they are doing. 
