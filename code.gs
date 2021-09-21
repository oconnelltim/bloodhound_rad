function doGet(e) {
  
  var recipient = e.parameter.recipient;
  var username = e.parameter.username;
  var subject = "Bloodhound: new study available"
  var message = "You have a new tracked study to view in Bloodhound, available at: http://ertrauma1.vch.ca/bloodhound/my_cases.php?username=" + username;                      
  MailApp.sendEmail(recipient, subject, message);
  return HtmlService.createHtmlOutput("Success!");

}
