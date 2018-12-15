<?php
# once I write a login script for this, I'll ensure the ajax request came from a logged in user
require('class.upload.php');
require('class.widget.php');
# new Upload object
$loader = new Upload('uploads/');
# Max file size is set in local .ini for 200 MB
$loader->setMaxSize(209715200); // Approx 200MB (remove the last two 00's to make it 2MB
# pass true here to append a "safe" suffix to potentially harmful files
$loader->allowAllTypes(false);
# Uploading the file
$loader->upload();
$messages = $loader->getMessages();
# pass true to JSON_ENCODE responses for AJAX
Widget::formatMessages($messages, true);