<?php
class ContactController{

    public function index(){
        include "./views/home/contact.php";
    }

    public function submit(){
        require_once "./models/Contact.php";
        $m=new Contact();
        $m->insert($_POST['fullname'],$_POST['email'],$_POST['message']);
        header("Location: index.php?url=contact");
    }
}
