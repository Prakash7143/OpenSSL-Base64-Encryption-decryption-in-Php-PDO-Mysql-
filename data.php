<?php
 $dbcon = new PDO("mysql:host=localhost;dbname=phpsecurity", "root", "");
 $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function decKey($ks){
    return strtr($ks,  '*-_','+/=');
}
$encryption_key_256bit = base64_encode(openssl_random_pseudo_bytes(32));
function my_decrypt($data, $key) {
    $encryption_key = base64_decode($key);
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}
$key = $encryption_key_256bit;


if ($_POST['action'] == "edit" || $_POST['action']=="delete"){
    switch($_POST['action']){
        case "edit":
            $edns =  $dbcon->prepare("SELECT * FROM data where id=".$_POST["id"]);
            $edns->execute();
            $row = $edns->fetch(PDO::FETCH_ASSOC);

          //  $row = mysqli_fetch_assoc(mysqli_query($dbcon,"SELECT * FROM data WHERE id = ".));
            $decrykey=  decKey($row["keyes"]);

            $edit_name = my_decrypt($row["name"], $decrykey);
            $edit_mail = my_decrypt($row["email"], $decrykey);
            $edit_pass = my_decrypt($row["password"], $decrykey);
            $edit_img = my_decrypt($row["userfile"], $decrykey);
            $xyz = '<img src="data:image/;base64,'.base64_encode($edit_img).'" style="height:50px; width:50px">';
            $pdf = '<iframe src="data:application/pdf;base64,'.base64_encode(my_decrypt($row['pdfdata'], $decrykey)).'" style="height:150px; width:100px; "  type="application/pdf"></iframe>';
            $merKey = $row['keyes'];
            echo $edit_name."#@#".$edit_mail."#@#".$edit_pass."#@#".$xyz."#@#".$pdf."#@#".$row['id']."#@#".$merKey;

        break;
        case "delete":
           $del = "DELETE  FROM data WHERE id = :id";
           $res = $dbcon->prepare($del);
           $res->execute(array(":id" => $_POST["id"]));
           $select = $dbcon->prepare("SELECT * FROM data");
           $select->execute();

            if($res){
                echo "Success";
            }
            else   
                echo "Failed";
    }

}


 
?>