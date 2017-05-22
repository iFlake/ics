<?php
namespace itais\ics\entity;

class Group
{
    public $uid;
    
    public $prename;
    public $postname;

    
    public function __construct($uid)
    {
        if (!is_int($uid)) throw new Exception("Expected integer for \$uid, got " . gettype($uid));
        $this->uid = $uid;
        $this->Load();
    }


    private function Load()
    {
        $table_groups = new \itais\ics\database\Table("groups");
        $data = $table_groups->Select(["prename", "postname"]);
        $this->prename = $data["prename"];
        $this->postname = $data["postname"];
    }


    public function SetPermission($name, $value, $id)
    {
        global $itais_ics_database_connection;
        if (!is_string($name)) throw new Exception("Expected string for \$name, got " . gettype($name));
        if (!is_int($value) || ($value < -1 || $value > 2)) throw new Exception("Expected \\itais\ics\\permission\\Permission for \$value, got " . gettype($name));
        if (!is_int($id)) throw new Exception("Expected integer for \$id, got " . gettype($id));
        $table_group_permissions = new \itais\ics\database\Table("group_permissions");
        if ($value == \itais\ics\permission\Permission::inherit && $this->GetRawPermission($name, $id) != \itais\ics\permission\Permission::inherit)
        {
            $table_group_permissions->Delete("uid = {this->uid } and name = '" . $itais_ics_database_connection->escape_string($name) . "' and id = {$id}");
        }
        else
        {
            if ($this->GetRawPermission($name, $id) == \itais\ics\permission\Permission::inherit)
            {
                $table_group_permissions->Insert(["uid" => $this->uid, "name" => $name, "value" => $value, "id" => $id]);
            }
            else
            {
                $table_group_permissions->Update(["name" => $name, "value" => $value, "id" => $id], "uid = {$this->uid} and name = '" . $itais_ics_database_connection->escape_string($name) . "' and id = {$id}");
            }
        }
    }

    public function GetPermission($name, $id = -1, $require_explicit = false)
    {
        if (!is_string($name)) throw new Exception("Expected string for \$name, got " . gettype($name));
        if (!is_int($id)) throw new Exception("Expected integer for \$id, got " . gettype($id));
        if (!is_bool($require_explicit)) throw new Exception("Expected boolean for \$require_explicit, got " . gettype($require_explicit));
        $permission = $this->GetRawPemission($name, $id);
        switch ($permission)
        {
            case \itais\ics\permission\Permission::allow:
            case \itais\ics\permission\Permission::allow_override:
                return true;
                break;
            case \itais\ics\permission\Permission::deny:
                return false;
                break;
            case \itais\ics\permission\Permission::inherit:
                return !$require_explicit;
                break;
        }
    }

    public function GetRawPemission($name, $id = -1)
    {
        global $itais_ics_database_connection;
        if (!is_string($name)) throw new Exception("Expected string for \$name, got " . gettype($name));
        if (!is_int($id)) throw new Exception("Expected integer for \$id, got " . gettype($id));
        $table_group_permissions = new \itais\ics\database\Table("group_permissions");
        $permissions = $table_group_permissions->Select(["value"], "name = '" . $itais_ics_database_connection->escape_string($name) . "' and id = {$id}");
        if (count($permissions) < 1) return \itais\ics\permissions\Permission::inherit;
        else return $permissions[0]["value"];
    }
}
