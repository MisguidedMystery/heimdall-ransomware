<?php
/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 12/10/16
 * Time: 14:30
 */
if(isset($_POST['action'])){
    $heimdall= new Heimdall();
    switch ($_POST['action']) {
        case 'criptography':
            $heimdall->criptographyByUrl($_POST['path']);
            exit();
            break;
        case 1:
            echo "i equals 1";
            break;
        case 2:
            echo "i equals 2";
            break;

    }
    exit();
}

?>

<html>
<head>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css">
    <script type="application/javascript" src="//code.jquery.com/jquery-1.12.3.js"></script>
    <script type="application/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script type="application/javascript" src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
    <script>

        $(document).ready(function() {
            $('#table1').DataTable();
            $('#sendPass').click(function (){
                i=1;
                $('#table > tbody  > tr').each(function() {
                    var $this = this;
                    setTimeout(function() {
                        path=$($this).find('td:first-child').html().trim();
                        $.ajax({
                            type: "post",
                            data: {action:'criptography', path: path,password:$('#password').val()},
                            success:function(data) {
                                $($this).find('td:last-child').html('encrypted');
                            }
                        });
                    }, 300*i);
                    i++;
                    //console.log($(this).find('td:first-child').html());

                });
//                $.get("?pathfile="+$(this).attr('data-path'), function(data) {
//                    $('#myModal').html(data);
//                });
            });
        });

    </script>
</head>

<body>
<?php

    ini_set('display_errors',1);
    ini_set('display_startup_erros',1);
    error_reporting(E_ALL);


    $heimdall = new Heimdall();
//    $file = $heimdall->readFile('/home/lenon/Workspace/wordpress/thema/circles_wp_3.5/Circles/attachment.php');
//    $fileCriptgrafy= $heimdall->cryptography($file);
//    $fileDesCriptgrafy= $heimdall->descryptography($fileCriptgrafy);
    ?>
    <fieldset>
        <legend>
            Heimdall
        </legend>
        <form id="formCriptography">
            <label>Password for cryptography</label>
            <input type="text" name="password" id="password">
            <input type="button" value="Send" id="sendPass">
        </form>
    </fieldset>
    <?php
    //list of files
    $arrList=$heimdall->getDirContents('/home/lenon/Workspace/wordpress');
    echo "Total of ".count($arrList)." files<br>";
    ?>
    <table id="table" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>
                    Path/File
                </th>
                <th>
                    Status
                </th>
            </tr>
        </thead>
        <tbody>
        <?php
            foreach($arrList as $file){
                ?>

                <tr>
                    <td>
                        <?php   echo $file;?>
                    </td>
                    <td>
                        <?php
                            if($heimdall->checkIfIsCriptograpfy($file)){
                                echo "encrypted";
                            }else{
                                echo "decrypted";
                            };

                        ?>
                    </td>
                </tr>
        <?php
            }
        ?>
        </tbody>
    </table>
</body>
</html>
<?php




//class Heimdall
class Heimdall{

    public $algorithm;
    public $password;
    public $IV;

    public function __construct()
    {
        $this->algorithm='AES-128-CBC';
        $this->IV='Gj4LL4rH0rn0nL33';
        $this->password='HeimdallF0r4ll';
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function cryptography($text){
        return "Heimdall---".openssl_encrypt($text, $this->algorithm, $this->password, 0, $this->IV);
    }

    public function descryptography($hash){
        $arrHash=explode("Heimdall---",$hash);
        return openssl_decrypt($arrHash[1], $this->algorithm, $this->password, 0, $this->IV);
    }

    public function getDirContents($dir, &$results = array()){
        $files = scandir($dir);

        foreach($files as $key => $value){
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if(!is_dir($path)) {
                $results[] = $path;
            } else if($value != "." && $value != "..") {
                self::getDirContents($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    }

    public function readFile($url){
        return file_get_contents($url);
//        $myfile = fopen($url, "r") or die("Unable to open file!");
//        $file= fread($myfile,filesize($url));
//        fclose($myfile);
//        return $file;
    }

    public function criptographyByUrl($url){
        $file = $this->readFile($url);
        $fileCriptgrafy= $this->cryptography($file);
        $arrSignature=explode("Heimdall---",$fileCriptgrafy);
        if(isset($arrSignature[1])){
            $this->editFile($url,$fileCriptgrafy);
            echo "ok";
        }else{
            echo "fail";
        }
    }

    private function editFile($url,$content){
        $fhandle = fopen($url,"w");
        fwrite($fhandle,$content);
        fclose($fhandle);
    }

    public function checkIfIsCriptograpfy($url){
        $file = $this->readFile($url);
        $arrSignature=explode("Heimdall---",$file);
        if(isset($arrSignature[1])){
            return true;
        }
        return false;
    }
}


