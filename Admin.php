<?php

require_once("DB.php");
require_once("Util.php");
class Admin
{
    private $conn;
    private $util;
    public $lastId;


    public function __construct()
    {
        $database = new DB();
        $utility = new Util();
        $this->util = $utility;
        $db = $database->doConnect();
        $this->conn = $db;
    }

    public function query($sql)
    {
        $stmt = $this->conn->prepare($sql);
        return $stmt;

    }

    public function addPackage($customer_id, $pname, $sname, $saddress, $recname, $readdress, $rephone, $remail, $sphone, $semail,
                               $delivered_by,$shipper_phone, $pstatus, $paweight, $paprice, $country_from, $country_to, $delivery_date,
                               $pimage, $pimage_tmp, $state_from, $state_to, $trackID)
    {

        try {

            $ext = explode('.', $pimage);

            $ext = strtolower(end($ext));
            $filename = uniqid(time(), true) . '.' . $ext;

            $queryins = "INSERT INTO package (customer_id,pname,sname,saddress,recname,readdress,rephone,remail,sphone,semail,delivered_by,shipper_phone,
                      pstatus,paweight,paprice,country_from,country_to,delivery_date,pimage,state_from,state_to,trackID) 
                     VALUES (:customer_id,:pname,:sname,:saddress,:recname,:readdress,:rephone,:remail,:sphone,:semail,:delivered_by,:shipper_phone,
                     :pstatus,:paweight,:paprice,:country_from,:country_to,:delivery_date,:pimage,:state_from,:state_to,:trackID)";
            $addPackage = $this->query($queryins);
            $addPackage->bindValue(":customer_id", $customer_id);
            $addPackage->bindValue(":pname", $pname);
            $addPackage->bindValue(":sname", $sname);
            $addPackage->bindValue(":saddress", $saddress);
            $addPackage->bindValue(":recname", $recname);
            $addPackage->bindValue(":readdress", $readdress);
            $addPackage->bindValue(":rephone", $rephone);
            $addPackage->bindValue(":remail", $remail);
            $addPackage->bindValue(":sphone", $sphone);
            $addPackage->bindValue(":semail", $semail);
            $addPackage->bindValue(":delivered_by", $delivered_by);
            $addPackage->bindValue(":shipper_phone", $shipper_phone);
            $addPackage->bindValue(":pstatus", $pstatus);
            $addPackage->bindValue(":paweight", $paweight);
            $addPackage->bindValue(":paprice", $paprice);
            $addPackage->bindValue(":country_from", $country_from);
            $addPackage->bindValue(":country_to", $country_to);
            $addPackage->bindValue(":delivery_date", $delivery_date);
            $addPackage->bindValue(":pimage", $filename);
            $addPackage->bindValue(":state_from", $state_from);
            $addPackage->bindValue(":state_to", $state_to);
            $addPackage->bindValue(":trackID", $trackID);
            $addPackage->execute();
            $this->lastId = $this->conn->lastInsertId();

            $path ="./../packageImage/".$filename;


            $this->util->compressImage($pimage_tmp, $path);
            return true;


        } catch (PDOException $ex) {
            throw new PDOException("Error:" . $ex->getMessage());
        }
    }

    public function getCustomers()
    {
        try {
            $customers_array = array();
            $getCustomers = $this->query("SELECT * FROM customer LIMIT 10");
            $getCustomers->execute();
            $getCustomers->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($getCustomers as $getCustomer)
                $customers_array[] = $getCustomer;
            return $customers_array;

        } catch (PDOException $ex) {
            throw new PDOException("Error Occured" . $ex->getMessage());
        }
    }



    public function insertAdmin($name, $username, $password, $email, $phone, $country, $state,$gender, $role,$address, $salt)
    {
        try {

            $stmt = $this->query('SELECT email FROM superadmin WHERE email = :cemail');
            $stmt->execute(array(':cemail' => $_POST['email']));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!empty($row['email'])){
                
                echo "Email Address supplied already exist for Another Admin.";
            }

            else {

            $stm = $this->query("INSERT INTO superadmin (name,username,password,email,phone,country,
                                  state,gender,role,address,salt)VALUES(:names,:username,:password,
                                  :email,:phone,:country,:state,:gender,:role,:address,:salt)");
            $stm->bindValue(":names", $name);
            $stm->bindValue(":username", $username);
            $stm->bindValue(":password", $password);
            $stm->bindValue(":email", $email);
            $stm->bindValue(":phone", $phone);
            $stm->bindValue(":country", $country);
            $stm->bindValue(":state", $state);
            $stm->bindValue(":gender", $gender);
            $stm->bindValue(":role", $role);
            $stm->bindValue(":address", $address);
            $stm->bindValue(":salt", $salt);
            $stm->execute();
            return $stm;

                } 

            }
            catch (PDOException $ex) {
            echo "Error::" . $ex->getMessage();
        }

            
    }



    public function UpdateAdmin($name, $username, $email, $phone, $country, $state,$address, $salt)
    {
        try {
            $stm = $this->query("UPDATE superadmin SET name = :name,username = :username,email = :email,
                                  phone = :phone,country = :country,state = :state,address = :address WHERE salt = :salt");
            $stm->bindValue(":name", $name);
            $stm->bindValue(":username", $username);
            $stm->bindValue(":email", $email);
            $stm->bindValue(":phone", $phone);
            $stm->bindValue(":country", $country);
            $stm->bindValue(":state", $state);
            $stm->bindValue(":address", $address);
            $stm->bindValue(":salt", $salt);
            $stm->execute();
            return $stm;

        } catch (PDOException $ex) {
            echo "Error::" . $ex->getMessage();
        }
    }
    public function UpdateAdminPassword($password,$salt)
    {
        try {
            $stm = $this->query("UPDATE  superadmin SET password = :password WHERE salt = :salt");
            $stm->bindValue(":password", $password);
            $stm->bindValue(":salt", $salt);
            $stm->execute();
            return $stm;

        } catch (PDOException $ex) {
            echo "Error::" . $ex->getMessage();
        }
    }

    public function CountPendingPackage(){
        try{
            $status = "Pending";
            $count = $this->query("SELECT COUNT(*) as id FROM package WHERE  pstatus = :status ");
            $count->bindParam(":status",$status);
            $count->execute();
            $data = $count->fetchColumn();

            return $data;

        }catch(PDOException $ex){
            throw  new PDOException("Error".$ex->getMessage());
        }

    }


    public function CountIntransitPackage(){
        try{
            $status = "In-transit";
            $count = $this->query("SELECT COUNT(*) as id FROM package WHERE  pstatus = :status ");
            $count->bindParam(":status",$status);
            $count->execute();
            $data = $count->fetchColumn();

            return $data;

        }catch(PDOException $ex){
            throw  new PDOException("Error".$ex->getMessage());
        }

    }
    public function CountTotalPackage(){
        try{

            $count = $this->query("SELECT COUNT(*) as id FROM package ");

            $count->execute();
            $data = $count->fetchColumn();

            return $data;

        }catch(PDOException $ex){
            throw  new PDOException("Error".$ex->getMessage());
        }

    }
    public function CountCustomer(){
        try{

            $count = $this->query("SELECT COUNT(*) as id FROM customer ");

            $count->execute();
            $data = $count->fetchColumn();

            return $data;

        }catch(PDOException $ex){
            throw  new PDOException("Error".$ex->getMessage());
        }

    }


    public function Account(){
        try{
            $account_arr = array();
            $account = $this->query("SELECT * FROM superadmin");
            $account->execute();
            $account->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($account as $acc){
                $account_arr[] = $acc;
            }
            return $account_arr;

        }catch (PDOException $ex){
            throw new PDOException("Error:".$ex->getMessage());
        }
    }


    public function DeleteAccount($salt){
        try {
            $co = count($salt);
            for ($i = 0; $i < $co; $i++) {
                $del = $this->query("DELETE FROM superadmin WHERE salt =:salt");
                $del->bindParam(':salt', $salt[$i]);
                $del->execute();

            }
            return true;
        }catch (PDOException $ex){
            throw new PDOException("Error:".$ex->getMessage());
        }
    }



    //Add customer

    public function insertCustomer($cusname, $address, $email, $password, $phone, $country, $state, $coname, $zip, $salt)
    {   
        $stmt = $this->query('SELECT cemail FROM customer WHERE cemail = :cemail');
        $stmt->execute(array(':cemail' => $_POST['email']));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($row['cemail'])){
            
            echo "Email Address supplied is already in use by Another Customer.";
        }

        else {

            try {
            $stm = $this->query("INSERT INTO customer(cname,caddress,cphone,cemail,cpassword,country,state,companyname,zipcode,salt)
 VALUES(:cusname,:address,:phone,:email,:password,:country,:state,:coname,:zip,:salt)");
            $stm->bindValue(":cusname", $cusname);
            $stm->bindValue(":address", $address);
            $stm->bindValue(":phone", $phone);
            $stm->bindValue(":email", $email);
            $stm->bindValue(":password", $password);
            $stm->bindValue(":country", $country);
            $stm->bindValue(":state", $state);
            $stm->bindValue(":coname", $coname);
            $stm->bindValue(":zip", $zip);
            $stm->bindValue(":salt", $salt);
            $stm->execute();
            return true;

            } catch (PDOException $ex) {
                echo "Error::" . $ex->getMessage();
            }
        }
        
        return true;
    }

    public function getPackage(){
        try{

            $package_arr = array();
            $select = $this->query("SELECT * FROM package ");
            $select->execute();
            $select->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($select as $items){
                $package_arr[] = $items;
            }
            return $package_arr;

        }catch (PDOException $ex){
            throw new PDOException("Error:".$ex->getMessage());
        }

    }
    public function getPendingPackage($status){
        try{
            $pack_arr = array();
            $count = $this->query("SELECT * FROM package WHERE  pstatus != :status ORDER BY pid DESC ");
            $count->bindParam(":status",$status);
            $count->execute();
            $count->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($count as $co){
                $pack_arr[] = $co;
            }

            return $pack_arr;

        }catch(PDOException $ex){
            throw  new PDOException("Error".$ex->getMessage());
        }

    }
    public function getDeliveredPackage($status){
        try{
            $delivered_arr = array();
            $count = $this->query("SELECT * FROM package WHERE  pstatus = :status ORDER BY pid DESC LIMIT 0,5 ");
            $count->bindParam(":status",$status);
            $count->execute();
            $count->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($count as $co){
                $delivered_arr[] = $co;
            }

            return $delivered_arr;

        }catch(PDOException $ex){
            throw  new PDOException("Error".$ex->getMessage());
        }

    }
    public function getStaffname($role){
        $staff_arr = array();
        $staff = $this->query("SELECT name,role FROM superadmin WHERE role = :role");
        $staff->bindParam(":role",$role);
        $staff->execute();
        $staff->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($staff as $staffs) {
            $staff_arr[] = $staffs;

        }
        return $staff_arr;


    }

    public function getProfile($salt){
        $profile_arr = array();
        $staff = $this->query("SELECT * FROM superadmin WHERE salt = :salt");
        $staff->bindParam(":salt",$salt);
        $staff->execute();
        $staff->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($staff as $staffs) {
            $profile_arr[] = $staffs;

        }
        return $profile_arr;


    }

    public function EditPackage($trackID){
        try{
            $packe_arr = array();

            $edit=$this->query("SELECT * FROM package WHERE trackID = :trackID");
            $edit->bindParam(":trackID",$trackID);
            $edit->execute();
            $edit->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($edit as $edited){
                $packe_arr[] = $edited;
            }
            return $packe_arr;

        }catch(PDOException $ex){
            throw new PDOException("Error:".$ex->getMessage());
        }

    }


    public function EditedPackage($delivered_by,$shipper_phone, $pstatus, $delivery_date,$trackID)
    {

        try {



          $queryEdit = "UPDATE package SET delivered_by = :delivered_by, shipper_phone = :shipper_phone,pstatus = :pstatus,
                             delivery_date = :delivery_date WHERE trackID = :trackID";
            $EditPackage = $this->query($queryEdit);

            $EditPackage->bindValue(":delivered_by", $delivered_by);
            $EditPackage->bindValue(":shipper_phone", $shipper_phone);
            $EditPackage->bindValue(":pstatus", $pstatus);
            $EditPackage->bindValue(":delivery_date", $delivery_date);
            $EditPackage->bindValue(":trackID", $trackID);
            $EditPackage->execute();

            return true;


        } catch (PDOException $ex) {
            throw new PDOException("Error:" . $ex->getMessage());
        }
    }
    public function setupApp($name,$footerlabel,$logo,$logo_tmp){
        try{

            $ext = explode('.',$logo);
            $ext = strtolower(end($ext));
            $logoname = uniqid(time(),true).'.'.$ext;

            $setup = $this->query("INSERT INTO setup (name,footerlabel,logo) VALUES(:name,:footerlabel,:logo)");
            $setup->bindParam(":name",$name);
            $setup->bindParam(":footerlabel",$footerlabel);
            $setup->bindParam(":logo",$logoname);
            $setup->execute();

            $path = "./../setup/".$logoname;
            $this->util->compressImage($logo_tmp,$path);
            return true;

        }catch(PDOException $ex){
            throw new PDOException("Error:".$ex->getMessage());
        }

    }

    public function getAppInfo(){
        try{
            $info = array();
            $getInfo = $this->query("SELECT * FROM setup");
            $getInfo->execute();
            $getInfo->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($getInfo as $getinfos);
            $info[] = $getinfos;
            return $info;

        }catch(PDOException $ex){
            throw new PDOException("Error:".$ex->getMessage());

        }
    }


}