<?php
namespace itais\ics\entity;

class User
{
    public $uid;
    public $data;
    public $primarygroup;
    public $groups;

    private $salt;
    private $password;


    public function __construct($uid = 1)
    {
        if (!is_int($uid)) throw new Exception("Expected integer for \$uid, got " . gettype($uid));
        $this->uid = $uid;
        $this->Load();
        $this->LoadGroups();
    }

    public static function FromSession($session)
    {
        if (!is_string($session)) throw new Exception("Expected string for \$session, got " . gettype($uid));
        global $itais_ics_database_connection;
        $table_sessions = new \itais\ics\database\Table("sessions");
        $sessions = $table_sessions->Select(["uid"], "session = '" . $itais_ics_database_connection->escape_string($session) . "'");
        if (count($sessions) < 1) return new User;
        else return new User($sessions[0]["uid"]);
    }


    private function Load()
    {
        $table_users = new \itais\ics\database\Table("users");
        $data = $table_users->Select(["salt", "password"], "uid = {$this->uid}");
        if (count($data) < 1) throw new Exception("Unknown user");
        $this->salt = $data["salt"];
        $this->password = $data["password"];
        $table_data_integer = new \itais\ics\database\Table("userdata_integer");
        $table_data_string = new \itais\ics\database\Table("userdata_string");
        $table_data_boolean = new \itais\ics\database\Table("userdata_boolean");
        array_merge($this->data, $table_data_integer->Select(null, "uid = {$this->uid}")[0]);
        array_merge($this->data, $table_data_string->Select(null, "uid = {$this->uid}")[0]);
        array_merge($this->data, $table_data_boolean->Select(null, "uid = {$this->uid}")[0]);
    }

    private function LoadGroups()
    {
        $table_primarygroup = new \itais\ics\database\Table("userdata_primarygroup");
        $table_groups = new \itais\ics\database\Table("userdata_groups");
        $primarygroup = $table_primarygroup->Select(null, "uid = {$this->uid}");
        $groups = $table_groups->Select(null, "uid = {$this->uid}");
        if (count($primarygroup) < 1) throw new Exception("Primary group does not exist for user {$this->uid}");
        $this->primarygroup = new Group($table_primarygroup[0]);
        foreach ($groups as $gid)
        {
            $this->groups[] = new Group($gid);
        }
    }


    public function CheckPassword($password)
    {
        if (!is_string($password)) throw new Exception("Expected string for \$password, got " . gettype($password));
        if (hash("whirlpool", $this->salt . $password) == $this->password) return true;
        else return false;
    }

    public function SetPassword($password)
    {
        if (!is_string($password)) throw new Exception("Expected string for \$password, got " . gettype($password));
        $this->password = hash("whirlpool", $this->salt . $password);
        $table_users = new \itais\ics\database\Table("users");
        $table_users->Update(["password" => $this->password], "uid = {$this->uid}");
    }


    public function SetPermission($name, $value, $id = -1)
    {
        global $itais_ics_database_connection;
        if (!is_string($name)) throw new Exception("Expected string for \$name, got " . gettype($name));
        if (!is_int($value) || ($value < -1 || $value > 2)) throw new Exception("Expected \\itais\ics\\permission\\Permission for \$value, got " . gettype($name));
        if (!is_int($id)) throw new Exception("Expected integer for \$id, got " . gettype($id));
        $table_user_permissions = new \itais\ics\database\Table("user_permissions");
        if ($value == \itais\ics\permission\Permission::inherit && $this->GetRawPermission($name, $id) != \itais\ics\permission\Permission::inherit)
        {
            $table_user_permissions->Delete("uid = {this->uid } and name = '" . $itais_ics_database_connection->escape_string($name) . "' and id = {$id}");
        }
        else
        {
            if ($this->GetRawPermission($name, $id) == \itais\ics\permission\Permission::inherit)
            {
                $table_user_permissions->Insert(["uid" => $this->uid, "name" => $name, "value" => $value, "id" => $id]);
            }
            else
            {
                $table_user_permissions->Update(["name" => $name, "value" => $value, "id" => $id], "uid = {$this->uid} and name = '" . $itais_ics_database_connection->escape_string($name) . "' and id = {$id}");
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
                $dominant = !$require_explicit;
                foreach ($this->groups as $group)
                {
                    $group_permission = $group->GetRawPermission($name, $id);
                    switch ($group_permission)
                    {
                        case \itais\ics\permission\Permission::allow:
                            $dominant = true;
                            break;
                        case \itais\ics\permission\Permission::allow_override:
                            return true;
                            break;
                        case \itais\ics\permission\Permission::deny:
                            return false;
                            break;
                        case \itais\ics\permission\Permission::inherit:
                            break;
                    }
                }
                return $dominant;
                break;
        }
    }

    public function GetRawPemission($name, $id = -1)
    {
        global $itais_ics_database_connection;
        if (!is_string($name)) throw new Exception("Expected string for \$name, got " . gettype($name));
        if (!is_int($id)) throw new Exception("Expected integer for \$id, got " . gettype($id));
        $table_user_permissions = new \itais\ics\database\Table("user_permissions");
        $permissions = $table_user_permissions->Select(["value"], "name = '" . $itais_ics_database_connection->escape_string($name) . "' and id = {$id}");
        if (count($permissions) < 1) return \itais\ics\permissions\Permission::inherit;
        else return $permissions[0]["value"];
    }


    public function GetSession()
    {
        $table_sessions = new \itais\ics\database\Table("sessions");
        $sessionid = bin2hex(random_bytes(128));
        $table_sessions->Insert(["session" => $sessionid, "uid" => $this->uid]);
        return $sessionid;
    }

    public function DisposeSession($session)
    {
        global $itais_ics_database_connection;
        $table_sessions = new \itais\ics\database\Table("sessions");
        $table_sessions->Delete("session = '" . $itais_ics_database_connection->escape_string($session) . "'");
    }
}
