<?php

$router->get('/', 'app/controllers/Pages/driver/index.php', true);
$router->get('/assignments', 'app/controllers/Pages/driver/assignments.php', true);
$router->get('/message-center', 'app/controllers/Pages/driver/message-center.php', true);
$router->get('/timesheet', 'app/controllers/Pages/driver/timesheet.php', true);
$router->get('/timesheet-review', 'app/controllers/Pages/driver/timesheet-review.php', true);
$router->get('/submitted-timesheets', 'app/controllers/Pages/driver/submitted-timesheets.php', true);
$router->get('/profile', 'app/controllers/Pages/driver/profile.php', true);
$router->get('/setprofilepicture', 'app/api/setprofilepicture.php');
$router->get('/counter', 'app/controllers/Pages/driver/counter.php', true);
$router->get('/faqs', 'app/controllers/Pages/driver/faqs.php', true);
$router->get('/preferences', 'app/controllers/Pages/driver/preferences.php', true);
$router->get('/getpreferences', 'app/api/getpreferences.php');
$router->get('/getprofile', 'app/api/getprofile.php');
$router->get('/getassignments', 'app/api/getassignments.php');
$router->get('/getoperationalassignments', 'app/api/getoperationalassignments.php');
$router->get('/assignment-operational-access', 'app/api/assignment-operational-access.php');
$router->get('/getstatus', 'app/api/getstatus.php');
$router->get('/emergencystatus', 'app/api/emergencystatus.php');
$router->get('/signature-access', 'app/api/signature-access.php');
$router->get('/signature-image', 'app/api/signature-image.php');
$router->get('/signature-status', 'app/api/signature-status.php');
$router->get('/connection-check', 'app/api/connection-check.php');
$router->get('/get-timesheet', 'app/api/get-timesheet.php');
$router->get('/get-timesheet-history', 'app/api/get-timesheet-history.php');
$router->get('/timesheet-pdf', 'app/api/timesheet-pdf.php');
$router->get('/get-timesheet-review', 'app/api/get-timesheet-review.php');
$router->get('/messenger-contacts', 'app/api/messenger-contacts.php');
$router->get('/messenger-retrieve-messages', 'app/api/messenger-retrieve-messages.php');
$router->get('/messenger-unread', 'app/api/messenger-unread.php');
$router->get('/messenger-group-members', 'app/api/messenger-group-members.php');

$router->post('/setstatus', 'app/api/setstatus.php');
$router->post('/signature-request', 'app/api/signature-request.php');
$router->post('/reuse-signature', 'app/api/reuse-signature.php');
$router->post('/driver-signature', 'app/api/driver-signature.php');
$router->post('/save-timesheet', 'app/api/save-timesheet.php');
$router->post('/review-timesheet', 'app/controllers/Actions/review-timesheet.php');
$router->post('/submit-timesheet', 'app/controllers/Actions/submit-timesheet.php');
$router->post('/messenger-conversation', 'app/api/messenger-conversation.php');
$router->post('/messenger-send-message', 'app/api/messenger-send-message.php');
$router->post('/messenger-mark-read', 'app/api/messenger-mark-read.php');
$router->post('/messenger-delete-conversations', 'app/api/messenger-delete-conversations.php');
$router->post('/messenger-create-group', 'app/api/messenger-create-group.php');
$router->post('/push-subscription', 'app/api/push-subscription.php');

$router->patch('/profile', 'app/controllers/Actions/updateprofileacct.php');
$router->patch('/setprofilepicture', 'app/api/setprofilepicture.php');
$router->patch('/assignmentstatushandler', 'app/api/assignmentstatushandler.php');
$router->patch('/assignments', 'app/controllers/Actions/updateassignment.php');
$router->patch('/updatepreferences', 'app/api/updatepreferences.php');

?>