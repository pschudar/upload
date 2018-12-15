<?php

/**
 * Upload
 *
 * @category File Upload
 * @package Upload
 * @copyright Copyright (c) 2015
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @version 0.6
 * @internal Created: 6.21.15 | Last Updated 7.18.15
 * 
 * Originally written to be an image upload class but any file type can be allowed.
 * It supports appending potentially dangerous files with an optional suffix.
 * By way of scandir() - this class can allow or prevent overwriting files with duplicate names, 
 * and automatically renames duplicate named files. Spaces in file names are replaced
 * with underscores
 * 
 * This class contains 5 public methods that allow for configuration changes.
 * 
 * setMaxSize() - Takes an integer and sets the max size for each upload; Overrides the default 2MB value.
 * The value given must be expressed in bytes. PHP v5.6.7-1 defaults upload_max_filesize within the php.ini file
 * to 2MB's. Therefore, the default max size is also 2MB.
 * 
 * getMaxSize() - Reports max size in Kilobytes formatted to one decimal place.
 * 
 * allowAllTypes() - Allows any type of file to be uploaded. By default, .upload is appended as a suffix
 * to files with file name extensions listed in the $_notTrusted property. To prevent the suffix from being
 * appended, pass false as an argument to this method.
 * 
 * upload() - Saves the file(s) to the destination directory. Spaces in file names are replaced by underscores.
 * Files w/the same name as an existing file are renamed by inserting a number in front of the file name extension.
 * To allow for file overwriting instead, pass false as an argument to this method.
 * 
 * getMessages() - Returns an array of messages reporting the status of uploads.
 * 
 * NOTE: Please ensure that the $_destination directory is writeable. chmod 777 /path/to/upload/dir
 * */
class Upload {

    /**
     * Contains an array of uploaded file information
     * 
     * @var array
     */
    protected $_uploaded = [];

    /**
     * The location to store uploaded images. The value is
     * set when an instance of Upload is created.
     * 
     * @var string
     */
    protected $_destination;

    /**
     * Max file size allowed to be uploaded, in bytes.
     * 1MB = 1,048,576 Bytes / 2MB = 2,091,752 Bytes
     * 
     * @var int
     */
    protected $_max = 2091752;

    /**
     * Contains an array of error messages
     * 
     * @var array
     */
    protected $_messages = [];

    /**
     * Contains an array of image MIME types accepted as an upload
     * Currently, it limits the permitted file types to .gif, .jpg and .png images
     *
     * @var array
     */
    protected $_permitted = [
        'image/gif',
        'image/jpeg',
        'image/pjpeg',
        'image/png'
    ];

    /**
     * Controls whether the MIME type will be checked against $_permitted.
     * If other file types should be allowed, call the allowAllTypes() method
     * before the upload() method. $_typeCheckingOn will then be false and any
     * type of file will be accepted for upload.
     * 
     * @var boolean
     */
    protected $_typeCheckingOn = true;

    /**
     * Contains an array of untrusted file extensions. If these types of files
     * are uploaded, they could present a security risk. To mitigate
     * such risks, we'll append an optional suffix to the file to render it harmless.
     * File extentions defined here do not contain a preceding dot (.)
     * 
     * @var array
     */
    protected $_notTrusted = ['bin', 'cgi', 'exe', 'js', 'pl', 'php', 'py', 'sh'];

    /**
     * Optional suffix to append to $_notTrusted file types
     * 
     * @var string
     */
    protected $_suffix = '.upload';

    /**
     * Contains a renamed file name
     * 
     * @var string
     */
    protected $_newName;

    /**
     * Renaming duplicate files needs to be optional
     * 
     * @var bool
     */
    protected $_renameDuplicates = true;

    /**
     * Takes a directory path as an argument that points to the directory
     * where you want to upload the file to and assigns it to $_destination.
     * 
     * @param $path
     */
    public function __construct($path) {
        if (!is_dir($path) || !is_writable($path)) {
            $this->_messages[] = '4' . $path . ' must be a valid, writable directory';
        }
        $this->_destination = $path;
    }

    /**
     * Initiates a series of tests on the file.
     * 
     * Defines $uploaded as first element of $_FILES array so
     * regardless of how the form defines the file, the code will run.
     * Renaming duplicate files needs to be optional, therefore we add it as an
     * argument to the upload method. It's assigned true by default. To prevent
     * duplicate file renaming, pass false as an argument to the upload() method.
     *
     * @param $renameDuplicates
     */
    public function upload($renameDuplicates = true) {
        $this->_renameDuplicates = $renameDuplicates;
        $uploaded = current($_FILES);
        if (is_array($uploaded['name'])) {
            foreach ($uploaded['name'] as $key => $value) {
                $currentFile['name'] = $uploaded['name'][$key];
                $currentFile['type'] = $uploaded['type'][$key];
                $currentFile['tmp_name'] = $uploaded['tmp_name'][$key];
                $currentFile['error'] = $uploaded['error'][$key];
                $currentFile['size'] = $uploaded['size'][$key];
                if ($this->checkFile($currentFile)) {
                    $this->moveFile($currentFile);
                }
            }
        } else {
            if ($this->checkFile($uploaded)) {
                $this->moveFile($uploaded);
            }
        }
    }

    public function getMessages() {
        return $this->_messages;
    }

    /**
     * Converts the value of $_max from bytes to Kilobytes.
     *
     * @return string
     */
    public function getMaxSize() {
        return number_format($this->_max / 1024, 1) . ' KB';
    }

    /**
     * Allows changing the maximum permitted file size
     * Checks the submitted value to ensure it's a number and
     * assigns it to the $_max property.
     *
     * @param $num
     */
    public function setMaxSize($num) {
        if (is_numeric($num) && $num > 0) {
            $this->_max = (int) $num;
        }
    }

    /**
     * Decides whether the MIME type should be checked.
     * 
     * $this->typeCheckingOn = false; will allow any file type
     * to be uploaded as no MIME types are inspected.
     * Ideally, only trusted users will be uploading files. So we're adding
     * $suffix as an argument here and define it as true to make it optional. If
     * it's defined as false, the value of $_suffix is defined as an empty string
     * and no suffix is appended to the file.
     *
     * @param $suffix
     */
    public function allowAllTypes($suffix = true) {
        $this->_typeCheckingOn = false; # leave this as false
        if (!$suffix) {
            $this->_suffix = ''; # define as .safe or whatever you want. This will allow all file types to be uploaded, but will append this suffix.
        }
    }

    /**
     * Checks for error level, file size and MIME type.
     * 
     * $accept is initialized to true; If the file fails any of the 
     * tests, $accept is set to false. The method will only return 
     * true if error level is 0, file size is within specified 
     * limits and MIME type is defined within the $_permitted array
     * If checkFile() returns false, getErrorMessage() is run which
     * returns an array of issues.
     *
     * @param $file
     * @return boolean
     */
    protected function checkFile($file) {
        $accept = true;
        if ($file['error'] != 0) {
            $this->getErrorMessage($file);
            if ($file['error'] == 4) {
                return false;
            } else {
                $accept = false;
            }
        }
        if (!$this->checkSize($file)) {
            $accept = false;
        }
        if ($this->_typeCheckingOn) {
            if (!$this->checkType($file)) {
                $accept = false;
            }
        }
        if ($accept) {
            $this->checkName($file);
        }
        return $accept;
    }

    /**
     * This is a switch statement that reports error levels on the uploaded file 
     * to add a suitable message to the $_messages array. If the error is 1 or 2, the
     * file is too large and the method returns false. 
     * The first integer in these messages determines it's formatting. 
     * See class.dashboardWidget.php for more details. dashboardWidget::formatMessages();
     * 
     * @param $file
     */
    protected function getErrorMessage($file) {
        switch ($file['error']) {
            case 1:
            case 2:
                $this->_messages[] = '3' . $file['name'] . ' exceeds the max upload size: (max: ' .
                        $this->getMaxSize() . ').';
                break;
            case 3:
                $this->_messages[] = '4' . $file['name'] . ' was only partially uploaded.';
                break;
            case 4:
                $this->_messages[] = '4 No file submitted.';
                break;
            default:
                $this->_messages[] = '4 There was a problem uploading ' . $file['name'];
                break;
        }
    }

    /**
     * Starts by checking the error level. If it's 1 or 3, the file is too big, so 
     * the method simply returns false and the error message was defined by the
     * getErrorMessage() method. The second check is if the reported size is 0. This
     * happens if the file is too big or no file was selected - these scenarios were covered 
     * by the getErrorMessage() method so the assumption is the file is empty.
     * Next, the reported size is compared to the $_max property, if the size is ok the
     * method returns true.
     *
     * @param $file
     * @return boolean
     */
    protected function checkSize($file) {
        if ($file['error'] == 1 || $file['error'] == 2) {
            return false;
        } elseif ($file['size'] == 0) {
            $this->_messages[] = '4' . $file['name'] . ' is an empty file.';
            return false;
        } elseif ($file['size'] > $this->_max) {
            $this->_messages[] = '4' . $file['name'] . ' exceeds the maximum size
                for a file (' . $this->getMaxSize() . ').';
            return false;
        } else {
            return true;
        }
    }

    /**
     * Checks the type reported by the $_FILES array against the $_permitted property.
     * If the type is included within $_permitted, the method returns true, else false and
     * the reason for rejection is added to the $_messages array.
     *
     * @param $file
     * @return boolean
     */
    protected function checkType($file) {
        if (in_array($file['type'], $this->_permitted)) {
            return true;
        } else {
            if (!empty($file['type'])) {
                $this->_messages[] = '4' . $file['name'] . ' is not a permitted file type.';
            }
            return false;
        }
    }

    /**
     * This method starts setting the _newName property to null. The Upload Class
     * allows for multiple file uploads and this property needs to be reset for each file.
     * str_replace then replaces spaces with underscores and assigns the result to $nospaces.
     * The value of $nospaces is compared with $file['name'] and if they're not the same, 
     * $nospaces is assigned as the value of the $_newName property.
     * 
     * We extract the file extension and assign it to the $extension variable.
     * We then add a suffix to the file only if the $_typeCheckingOn property is false and
     * the $_suffix property is not an empty string. It's also a good idea to add the suffix
     * to files that lack an extension as they're typically executable files on linux servers.
     *
     * @param $file
     */
    protected function checkName($file) {
        $this->_newName = null;
        $unwantedArray = [' ', '-']; # replace spaces, hyphens
        foreach ($unwantedArray as $remove) {
            $nospaces = str_replace($remove, '_', $file['name']);
        }
        if ($nospaces != $file['name']) {
            $this->_newName = $nospaces;
        }
        $extension = pathinfo($nospaces, PATHINFO_EXTENSION);
        if (!$this->_typeCheckingOn && !empty($this->_suffix)) {
            if (in_array($extension, $this->_notTrusted) || empty($extension)) {
                $this->_newName = $nospaces . $this->_suffix;
            }
        }
        if ($this->_renameDuplicates) {
            $name = isset($this->_newName) ? $this->_newName : $file['name'];
            $existing = scandir($this->_destination);
            if (in_array($name, $existing)) {
                // rename file
                $basename = pathinfo($name, PATHINFO_FILENAME);
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $i = 1;
                do {
                    $this->_newName = $basename . '_' . $i++;
                    if (!empty($extension)) {
                        $this->_newName .= ".$extension";
                    }
                } while (in_array($this->_newName, $existing));
            }
        }
    }

    /**
     * Wraps move_uploaded_file internally and returns true if 
     * file upload is successful. Success or Error message is added
     * to the stack of info contained within $_messages array. If the
     * file name was changed by removing spaces or adding a suffix or both the
     * moveFile method uses the amended name when saving the file to $destination.
     * 
     * It's good practice in my opinion to alert users of a filename change so
     * I also output this within the $_messages array
     *
     * @param $file
     */
    protected function moveFile($file) {
        $filename = isset($this->_newName) ? $this->_newName : $file['name'];
        $success = move_uploaded_file($file['tmp_name'], $this->_destination . $filename);
        if ($success) {
            $result = '1' . $file['name'] . ' was uploaded successfully';
            if (!is_null($this->_newName)) {
                $result .= ' and renamed ' . $this->_newName;
            }
            $this->_messages[] = $result;
        } else {
            $this->_messages[] = '4 Could not upload ' . $file['name'];
        }
    }

}
