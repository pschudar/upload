<?php

class Widget {

    /**
     * This static method accepts an error message with a pre-designated error # and formats it
     * as an unordered list. The error number determines the color of the message.
     * 
     * Optionally it accepts a boolean - true to json_encode <li> only
     * 
     * @param str $msgs messages to format.
     * @param bool $json optional arg that determines whether to json_encode the msgs 
     */
    public static function formatMessages($msgs, $json = false) {
        $ulo = '<ul id="addInvStatus" class="list-group">';
        $li = null;
        $err = 'false';
        foreach ($msgs as $msg) {
            $label = substr($msg, 0, 1);
            switch ($label) {
                case 1:
                    $class = 'success';
                    $icon = 'check';
                    break;
                case 2:
                    $class = 'info';
                    $icon = 'info-circle';
                    break;
                case 3:
                    $class = 'warning';
                    $icon = 'exclamation-circle';
                    $err = 'true';
                    break;
                case 4:
                    $class = 'danger';
                    $icon = 'exclamation-triangle';
                    $err = 'true';
                    break;
                case 5:
                    # custom alert for add image - edit item box
                    $class = 'info';
                    $icon = 'picture';
                    break;
                case 6:
                    # custom alert for add new inventory item & blog category
                    $class = 'info';
                    $icon = 'plus';
                    break;
            }
            # Replace only the first instance of alert indicator.
            $msg = preg_replace("/$label/", '', $msg, 1);

            $li .= "<li id=\"statusList\" class=\"list-group-item list-group-item-$class\"><span class=\"vl vl-$icon\"></span> $msg</li>";
        }
        $ulc = '</ul>';
        switch ($json) {
            case false:
                echo "$ulo $li $ulc";
                break;
            case true:
                $arr = ['error' => $err, 'class' => $class, 'icon' => $icon, 'msg' => $msg];
                echo json_encode($arr);
                break;
        }
    }

}
