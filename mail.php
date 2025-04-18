<?php
$check=mail("To: kush2005m@gmail.com","Test Email", "This is a test email", "From: kushmehta124@gmail.com");
if($check){
    echo "Email sent successfully";
}else{
    echo "Email sending failed";
}   



?>