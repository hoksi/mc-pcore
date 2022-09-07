<?php


/**
 * Description of Shared
 *
 * @author hoksi
 */
class Shared {

    public $id = 0;
    public $filename = '';
    public $filepath = '';
    public $filepointer;
    public $data = array();
    public $date = 0;

    function __construct($id) {
        $this->id = $id;

        if ($this->filepath) {
            $this->filename = $this->filepath . $this->id;
        } else {
            $this->filename = kSHARED_FOLDER . $this->id;
        }

        if (empty($this->filename)) {
            log_message('error', "Shared no filename");
            return false;
        }

        $this->date = ($_SESSION[(kSESSION_SHARED . $id)] ?? '');
    }

    function SetFilePath() {
        if ($this->filepath) {
            $this->filename = $this->filepath . $this->id;
        } else {
            $this->filename = kSHARED_FOLDER . $this->id;
        }
    }

    function clear() {
        if ($this->id == null) {
            return false;
        }

        $counter = 0;
        ignore_user_abort(true);
        if (($this->filepointer = @fopen($this->filename, "w")) == false) {
            ignore_user_abort(false);
            return false;
        }

        while (true) {
            if ($counter >= kSHARED_MAX_ATTEMPS) {
                fclose($this->filepointer);
                ignore_user_abort(false);
                return false;
            }

            if (flock($this->filepointer, LOCK_EX) == false) {
                $counter++;
                usleep(rand(1, 25000));
            } else
                break;
        }

        if (flock($this->filepointer, LOCK_UN) == false) {
            ignore_user_abort(false);
            return false;
        }

        unset($this->data);
        $this->data = array();

        fclose($this->filepointer);
        $this->date = $_SESSION[kSESSION_SHARED . $id] = filemtime($this->filename);
        ignore_user_abort(false);

        return true;
    }

    function setObjectForKeyClear($key) {
        if ($this->id == null)
            return false;

        $counter = 0;
        ignore_user_abort(true);
        //echo $this->filename;
        if (($this->filepointer = @fopen($this->filename, "a+")) == false) {
            ignore_user_abort(false);
            print "can not open file<br>";
            return false;
        }

        while (true) {
            if ($counter >= kSHARED_MAX_ATTEMPS) {
                fclose($this->filepointer);
                print("1 aborted...");
                ignore_user_abort(false);
                return false;
            }

            $block;
            if (flock($this->filepointer, LOCK_EX, $block) == false) {
                $counter++;
                print("1 waiting...");
                usleep(rand(1, 25000));
            } else
                break;
        }

        $data = file_get_contents($this->filename);
        $array = array();
        if (!empty($data))
            $array = unserialize($data);

        unset($array[$key]);

        $data = serialize($array);
        ftruncate($this->filepointer, 0);
        fseek($this->filepointer, 0, SEEK_SET);
        fwrite($this->filepointer, $data);

        $this->data = $array;

        if (flock($this->filepointer, LOCK_UN) == false) {
            ignore_user_abort(false);
            return false;
        }

        fclose($this->filepointer);
        $this->date = $_SESSION[kSESSION_SHARED . $id] = filemtime($this->filename);
        ignore_user_abort(false);

        return true;
    }

    function setObjectForKey($value, $key) {
        if ($this->id == null)
            return false;

        $counter = 0;
        ignore_user_abort(true);
        //echo $this->filename;
        if (($this->filepointer = @fopen($this->filename, "a+")) == false) {
            ignore_user_abort(false);
            print "({($this->filename}) can not open file<br>";
            return false;
        }

        while (true) {
            if ($counter >= kSHARED_MAX_ATTEMPS) {
                fclose($this->filepointer);
                print("1 aborted...");
                ignore_user_abort(false);
                return false;
            }

            $block;
            if (flock($this->filepointer, LOCK_EX, $block) == false) {
                $counter++;
                print("1 waiting...");
                usleep(rand(1, 25000));
            } else
                break;
        }

        $data = file_get_contents($this->filename);
        $array = array();
        if (!empty($data))
            $array = unserialize($data);

        $array[$key] = $value;
        $data = serialize($array);
        ftruncate($this->filepointer, 0);
        fseek($this->filepointer, 0, SEEK_SET);
        fwrite($this->filepointer, $data);

        $this->data = $array;

        if (flock($this->filepointer, LOCK_UN) == false) {
            ignore_user_abort(false);
            return false;
        }

        fclose($this->filepointer);
        $this->date = $_SESSION[kSESSION_SHARED . ($id ?? '')] = filemtime($this->filename);
        ignore_user_abort(false);

        return true;
    }

    function getObjectForKey($key)
    {
        if ($this->id == null) {
            return '';
        } else {
            $array = array();
            if (file_exists($this->filename)) {
                $data = file_get_contents($this->filename);
                if (!empty($data)) {
                    $array = @unserialize($data);
                }
            }

            return $array[$key] ?? '';
        }
    }

}
