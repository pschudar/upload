<?php
require('class.upload.php');
require('class.widget.php');
$loader = new Upload('uploads/');
$loader->setMaxSize(209715200); // Approx 200MB (remove the last two 00's to make it 2MB
$loader->allowAllTypes(false);
$loader->upload();
$messages = $loader->getMessages();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>Backup Utility</title>
        <!-- Bootstrap -->
        <link href="assets/css/bootstrap.min.css" rel="stylesheet">
        <!-- Fontello -->
        <link href="assets/css/vl-fontello.css" rel="stylesheet" type="text/css">
        <link href="assets/css/bk.css" rel="stylesheet" type="text/css">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
            <div class="container">
                <a class="navbar-brand" href="backup.php"><span class="vl vl-cloud"></span> MyCloud</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item active">
                            <a class="nav-link" href="backup.php">Home
                                <span class="sr-only">(current)</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container">
            <br>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active"><span class="vl vl-home"></span> Home</li>
                    </ol>
                </nav>
            <div class="row">
                <div class="col-md-7">
                    <div id="leftContent" class="card">

                        <div class="card-body">
                            <h6 id="leftHeading" class="card-title"><span class='vl vl-sliders'></span> Pending Uploads <span class='float-right text-info vl vl-question-circle-o'></span></h6>
                            <div id="leftCard" class="card-text">

                            </div>
                        </div>
                    </div>
                </div>
                <div id="uploadCard" class="col-md-5">
                    <div class="card">

                        <div class="card-body">
                            <h6 class="card-title"><span class='vl vl-upload-cloud'></span> Upload Multiple Files <span class="vl vl-info-circle float-right text-primary"></span></h6>
                            <p class="card-text"></p>

                            <ul id="msgList" class="list-group">
                                <li id="notice" class="list-group-item list-group-item-info"><span class="vl vl-info-circle"> Drag and Drop is enabled (Drop onto Browse button)</span></li>
                            </ul>

                            <form role="form" id="uploadFiles" class="form-horizontal" action="#" method="post" style='display: block;' enctype="multipart/form-data">
                                <p><br>
                                    <label class="sr-only" for="fileInput">Upload Files:</label>
                                    <input type="hidden" name="MAX_FILE_SIZE" id="MAX_FILE_SIZE" value="209175200">
                                    <input type="file" class="form-control" name="files[]" id="fileInput" multiple>
                                </p>
                                <p>
                                    <button for="fileInput" type="submit" class="btn btn-success" name="upload" id="submit" value="Upload"> 
                                        <span class="vl vl-upload"></span> Upload Files
                                    </button> 

                                    <button id="reset" name="reset" type="reset" class=" btn btn-danger">
                                        <span class="vl vl-block"></span> Reset
                                    </button>
                                </p>
                            </form>
                        </div>
                    </div>
                    <br>
                    <div class="card">
                        <div class="card-body">
                            <h6 id='imgHeader' class="card-title"><span class='vl vl-bell-o text-secondary'></span> Status Messages <span class="vl vl-question-circle-o float-right text-primary"></span></h6>
                            <div id="statusList" class="card-text">
                                <?php
                                if (isset($_POST['upload']) && !empty($messages)) {
                                    Widget::formatMessages($messages);
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/bootstrap-filestyle.js"></script>
        <script src="assets/js/bk.js"></script>
        <script src="assets/js/upload.js"></script>
    </body>
</html>
