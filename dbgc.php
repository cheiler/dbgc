<?php
/**
 * Created by PhpStorm.
 * User: christianheiler
 * Date: 14/01/2019
 * Time: 22:43
 */

class dbgc
{
    private $user = "";
    private $password = "";
    private $database = "";
    private $port=3306;
    private $host = "127.0.0.1";
    private $link;
    private $success;
    private $report = array();
    private $debug = false;

    public function __construct($user, $password, $database, $host="127.0.0.1", $port="3306")
    {
        $this->link = mysqli_init();
        $this->success = mysqli_real_connect(
            $this->link,
            $host,
            $user,
            $password,
            $database,
            $port
        );
        if($this->success == true){
            $msg = "Database Connection Success";
        } else {
            $msg = "Database Connection Failure";
        }
        $this->reporting($msg);
    }

    /**
     * @param $message
     */
    private function reporting($message){
        $line = date("Y-m-d H:i:s")." | ".$message;
        $this->report[] = $line;
        if($this->debug){
            echo "\n<!-- $line -->\n";
        }
    }

    /**
     * @param $table
     * @param $data_array
     * @return bool
     */
    public function insert_or_update($table, $data_array){
        $query = "INSERT INTO `$table` ";
        $query .= "(";
        $element_string = "";
        $data_string = "";
        $update_string = "";
        foreach($data_array as $key => $value){
            $value = mysqli_real_escape_string($this->link, $value);
            if($data_string != ""){
                $data_string .= ", ";
            }
            $data_string .= "`$key`";

            if($element_string != ""){
                $element_string .= ", ";
            }
            $element_string .= "'$value'";
            if($update_string != ""){
                $update_string .= ", ";
            }
            $update_string .= "`$key`='$value'";

        }
        $query .= $data_string.") ";
        $query .= "VALUES ($element_string)";
        $query .= "ON DUPLICATE KEY UPDATE $update_string";

        if($this->link->real_query($query)){
            return true;
        } else {
            echo "$query \n\n\n";
            print_r($this->link->error);
        }
        return false;

    }

    /**
     * @param $table
     * @param $fields
     * @param $where_clause
     * @return array|null
     */
    public function read($table, $fields, $where_clause){

        $fields_string = "";
        foreach($fields as $field){
            if($fields_string!=""){$fields_string .= ", ";}
            $fields_string .= $field;
        }

        $query = "SELECT $fields_string FROM $table WHERE $where_clause";

        if($this->link->real_query($query)){
            if ($result = $this->link->use_result()) {
                while ($row = $result->fetch_row()) {
                    foreach($row as $key => $value){
                        $this_row[$fields[$key]] = $value;
                    }


                    $out[] = $this_row;

                }
                $result->close();
                return $out;
            }

        } else {
            echo "$query \n\n\n";
            print_r($this->link->error);
        }
        return null;


    }

    /**
     * @param $fullquery
     * @return array|null
     */
    public function query($fullquery){
        $out = array();
        //$query = mysqli_real_escape_string($this->link,$fullquery);
        if($this->link->real_query($fullquery)){
            if ($result = $this->link->use_result()) {
                while ($row = $result->fetch_row()) {
                    $out[] = $row;
                }
                $result->close();
                return $out;
            }
        } else {
            echo "$query \n\n\n";
            print_r($this->link->error);
        }
        return null;
    }


    /**
     * @param $table
     * @param $search_fieldname
     * @param $search_field_value
     * @param array $data_array
     * @return bool
     */
    public function update_by_field($table, $search_fieldname, $search_field_value, $data_array = array()){
        $query = "UPDATE `$table` SET ";
        $update_string = "";
        foreach($data_array as $key => $value){
            $value = mysqli_real_escape_string($this->link, $value);
            if($update_string != ""){
                $update_string .= ", ";
            }
            $update_string .= "`$key`='$value'";

        }

        $search_fieldname = mysqli_real_escape_string($this->link, $search_fieldname);
        $search_field_value = mysqli_real_escape_string($this->link, $search_field_value);

        $query .= " $update_string ";
        $query .= "WHERE `$table`.`$search_fieldname` = '$search_field_value'";


        if($this->link->real_query($query)){
            return true;
        } else {
            echo "$query \n\n\n";
            print_r($this->link->error);
        }
        return false;
    }


    public function close(){
        $this->link->close();
    }


}
