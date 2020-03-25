<?php
    ini_set("display_errors","0");
    $conn = new PDO("mysql:host=localhost;dbname=phpsecurity", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $encryption_key_256bit = base64_encode(openssl_random_pseudo_bytes(32));

    function my_encrypt($data, $key) {
        $encryption_key = base64_decode($key);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    function my_decrypt($data, $key) {
        $encryption_key = base64_decode($key);
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }
    $key = $encryption_key_256bit;
    

    if(isset($_POST['submit_btn'])){

        $sql = "INSERT INTO data (name, email, password, userfile, pdfdata, keyes)
        VALUES (:name, :email, :password, :ufile, :pdfdata, :keyes)";

        $stmt = $conn->prepare($sql);
        $nm = my_encrypt($_POST['name'], $key);
        $ml = my_encrypt($_POST['email'], $key);
        $pd = my_encrypt($_POST['password'], $key);
        $pic = my_encrypt(file_get_contents($_FILES['ufile']['tmp_name']), $key);
        $pdf = my_encrypt(file_get_contents($_FILES['pdf']['tmp_name']), $key);

        $stmt->bindParam(':name', $nm, PDO::PARAM_STR); 
        $stmt->bindParam(':email', $ml, PDO::PARAM_STR);
        $stmt->bindParam(':password', $pd, PDO::PARAM_STR);
        $stmt->bindParam(':keyes',  $key, PDO::PARAM_STR);
        $stmt->bindParam(':ufile', $pic, PDO::PARAM_STR);
        $stmt->bindParam(':pdfdata', $pdf, PDO::PARAM_STR);

        $stmt->execute(); 
       if($sql){
           echo '<script>alert("inserted")</script>';
       }else{
            echo 'failed to insert';
       }
    
    
    }

   
    if(isset($_POST['edit_btn'])){
        $pdfcheck = $_FILES['pdf']['tmp_name'];
        $ufile = $_FILES['ufile']['tmp_name'];

        if($pdfcheck == '' && $ufile == ''){
            $update = "UPDATE data SET name=:name, email=:email, password=:password WHERE id=:update";
            $stmt = $conn->prepare($update);
            if($update){
                echo '<script>alert("updated")</script>';
            }else{
                 echo 'failed to insert';
            }
            
        }
        $mks = $_POST['myk'];
        if($pdfcheck != '' && $ufile == ''){
            $update = "UPDATE data SET name=:name, email=:email, password=:password, pdfdata= :pdfdata WHERE id=:update";
            $stmt = $conn->prepare($update);
            if($update){
                echo '<script>alert("updated")</script>';
            }else{
                 echo 'failed to insert';
            }
            $pdf = my_encrypt(file_get_contents($_FILES['pdf']['tmp_name']), $mks);
            $stmt->bindParam(':pdfdata', $pdf, PDO::PARAM_STR);
            
        }
        if($pdfcheck == '' && $ufile != ''){
            $update = "UPDATE data SET name=:name, email=:email, password=:password, userfile= :ufile WHERE id=:update";
            $stmt = $conn->prepare($update);
            if($update){
                echo '<script>alert("updated")</script>';
            }else{
                 echo 'failed to insert';
            }
            $pic = my_encrypt(file_get_contents($_FILES['ufile']['tmp_name']), $mks);
            $stmt->bindParam(':ufile', $pic, PDO::PARAM_STR);
            
        }
        if($pdfcheck != '' && $ufile != ''){
            $update = "UPDATE data SET name=:name, email=:email, password=:password, userfile= :ufile,  pdfdata= :pdfdata  WHERE id=:update";
            $stmt = $conn->prepare($update);
            if($update){
                echo '<script>alert("updated")</script>';
            }else{
                 echo 'failed to insert';
            }
            $pdf = my_encrypt(file_get_contents($_FILES['pdf']['tmp_name']), $mks);
            $pic = my_encrypt(file_get_contents($_FILES['ufile']['tmp_name']), $mks);

            $stmt->bindParam(':ufile', $pic, PDO::PARAM_STR);
            $stmt->bindParam(':pdfdata', $pdf, PDO::PARAM_STR);
            
        }
            
            $nm = my_encrypt($_POST['name'], $mks);
            $ml = my_encrypt($_POST['email'], $mks);
            $pd = my_encrypt($_POST['password'], $mks);
            

            $stmt->bindValue(":name", $nm);
            $stmt->bindValue(":email", $ml);
            $stmt->bindValue(":password", $pd);
            //$stmt->bindValue(":keyes", $mks);
            $stmt->bindValue(":update", $_POST['update']);
            $stmt->execute();
        
    }

    $select = $conn->prepare("SELECT * FROM data");
    $select->execute();
?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<title>PhP Data Security</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script> -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.1/parsley.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    </head>
    <body>
        <div class="container">
            <form class="form-group" id="dataForms" enctype="multipart/form-data" method="post" action="">
            <br><br>
              <input type="hidden"  id="update" name="update">
              <input type="hidden"  id="myk" name="myk">
                <div class="row">
                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                        <label id="yourName" style="display:none;">Your Name</label>
                        <input class="form-control"  id="name" name="name" placeholder="Enter Your Name">
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                        <label id="yourMail" style="display:none;">Your Email</label>
                        <input type="email" class="form-control"  id="email" name="email" placeholder="Enter Your Email">
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                        <label id="yourPass" style="display:none;">Your Password</label>
                        <input type="password" class="form-control"  id="password" name="password" placeholder="Enter Your Password">
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                        <label>Profile</label>
                        <span id="img"></span>
                        <input type="file" class="form-control"  id="ufile" name="ufile" >
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                        <label>PDF</label>
                        <span id="pdf_data"></span>
                        <input accept="application/pdf" type="file" class="form-control"  id="pdf" name="pdf" >
                    </div><br><br>
                    <div class="col-xl-12 col-lg-12 col-md-6 col-sm-12">
                        <div class="text-left mt-3">
                            <button class="btn btn-info" type="submit" name="edit_btn" id="edit_btn" style="display:none;">Update Form</button>
                            <button class="btn btn-danger" type="button" onclick="getCancel()" name="cancel" id="cancel" style="display:none;">Cancel</button>
                            
                            <button class="btn btn-info" type="submit" name="submit_btn" id="submit_btn">Submit Form</button>
                        </div>
                    </div>
                </div>
            </form>
            <table class="table table-bordered">
                <thead>
                    <th>Sr</th>
                    <th>Name</th>
                    <th>Email Id</th>
                    <th>Password</th>
                    <th>Image</th>
                    <th>PDF</th>
                    <th>EDIT</th>
                    <th>DELETE</th>
                </thead>
                <tbody>
                    <?php                  
                
                        $p=1;
                        while($q = $select->fetch(PDO::FETCH_ASSOC)){
                            echo '<tr>
                            <td>'.$p++.'</td>
                            <td>'.my_decrypt($q['name'], $q['keyes']).'</td>
                            <td>'.my_decrypt($q['email'], $q['keyes']).'</td>
                            <td>'.my_decrypt($q['password'], $q['keyes']).'</td>
                            <td><img src="data:image/;base64,'.base64_encode(my_decrypt($q['userfile'], $q['keyes'])).'" style="height:50px; width:50px"></td>

                            <td><iframe src="data:application/pdf;base64,'.base64_encode(my_decrypt($q['pdfdata'], $q['keyes'])).'" style="height:150px; width:100px; "  type="application/pdf"></iframe></td>
                            <td><button name="editBtn" id="editVal'.$q['id'].'" type="button" onclick="editValues('.$q['id'].')" class="btn btn-info">EDIT</button></td>
                            <td><button name="deleteVal" id="deleteVal'.$q['id'].'" onclick="deleteData('.$q['id'].')" type="button" class="btn btn-danger">DELETE</button></td>
                            </tr>';
                        }
                    ?>
                </tbody>
            </table>
          
       </div>
    </body>

</html>
<script>
function Ajax(Type, URL, URLData)
    {
    var Responses = "";
        $.ajax(
        {
            type: Type,
            async: false,
            cache: false,
            url: URL,
            data: URLData,
            dataType: 'html',
            success:function(result, Response, jqXHR)
            {
                Responses = result;
            }
        });
        return Responses;
    }
    function editValues(ids){
		result = Ajax("POST", "data.php", "&action=edit&id="+ ids).split("#@#");
		
		$("#name").val(result[0]);
		$("#email").val(result[1]);
		$("#password").val(result[2]);
        $("#img").html(result[3]);
        $("#pdf_data").html(result[4]);
        $("#update").val(result[5]);
        $("#myk").val(result[6]);

        $("#cancel, #edit_btn, #yourName, #yourMail, #yourPass").css("display", "block");
        $("#submit_btn").css("display", "none");
        
    }
    function deleteData(id)
	{  
		result = Ajax("POST", "data.php", "&action=delete&id=" + id).split("#@#");
        if(result[0] == "Success"){
            alert("Deleted Successfully");
            window.location.replace('');
        }else{
            alert("Failed To Delete");
            window.location.replace('');
        }   
    }

    function getCancel() {
        window.location.replace('');
    }
</script>
