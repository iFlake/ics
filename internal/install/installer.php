<?php
if ($REQUEST == "/")
{
    ?>
    <!DOCTYPE HTML>
    <html>
        <head>
            <title>itais community suite - install</title>
            <style>
                @import url('https://fonts.googleapis.com/css?family=Lato:300,400,700');
                body
                {
                    margin: 0 0;
                    padding: 0 0;
                    background-color: #E9E9E9;
                }
                a
                {
                    text-decoration: none;
                }
                .its-header-container
                {
                    width: 100%;
                    height: 200px;
                    background: linear-gradient(141deg, #0fb8ae 0%, #1fc8dc 51%, #2cb5e9 75%);
                    box-shadow: 0 1px 1px 0 #A2A2A2;
                }
                .its-header-contents
                {
                    margin: 0 15%;
                    font-family: lato;
                    font-weight: 300;
                    font-size: 48px;
                    line-height: 200px;
                    color: #FFFFFF;
                }
                .its-main-container
                {
                    width: 70%;
                    margin: 24px 15%;
                }
                .its-map
                {
                    margin: 14px 0;
                }
                .its-map-link
                {
                    font-family: lato;
                    color: #00A2C4;
                }
                .its-map-next
                {
                    font-family: lato;
                    font-weight: 700;
                    margin: 0 5px;
                    color: #646464;
                }
                .its-invisible
                {
                    display: none;
                }
                .its-node
                {
                    width: 100%;
                    border-top: #00C7D2 solid 2px;
                    box-shadow: 0 1px 1px 0 #D9D9D9;
                    margin: 24px 0;
                }
                .its-node-info
                {
                    border-top: #00A200 solid 2px;
                }
                .its-node-error
                {
                    border-top: #FF0000 solid 2px;
                }
                .its-node-button
                {
                    border: #E2E2E2 solid 2px;
                    cursor: pointer;
                    transition: border 0.32s ease-in-out;
                }
                .its-node-button:hover
                {
                    border: #00C7D2 solid 2px;
                }
                .its-node-titlebox
                {
                    width: 100%;
                    height: 48px;
                    background-color: #FFFFFF;
                }
                .its-node-title
                {
                    margin-left: 24px;
                    line-height: 48px;
                    font-family: lato;
                    font-weight: 400;
                    font-size: 12px;
                    text-transform: uppercase;
                }
                .its-node-title-notransform
                {
                    text-transform: none;
                }
                .its-node-element
                {
                    width: 100%;
                    height: 100px;
                    background-color: #F2F2F2;
                    border-top: #E2E2E2 solid 1px;
                    position: relative;
                }
                .its-node-checkbox-container
                {
                    position: absolute;
                    left: 72px;
                    top: 0;
                    height: 100%;
                }
                .its-node-checkbox-border
                {
                    height: 100%;
                    width: 1px;
                    background-color: #E2E2E2;
                    position: relative;
                    display: inline-block;
                }
                .its-node-checkbox
                {
                    width: 32px;
                    height: 32px;
                    left: -16px;
                    top: 30px;
                    border-radius: 16px;
                    background-color: #FFFFFF;
                    border: #E2E2E2 solid 1px;
                    position: absolute;
                    display: inline-block;
                }
                .its-node-check
                {
                    width: 16px;
                    height: 16px;
                    border-radius: 12px;
                    margin: 8px auto;
                    background-color: #A2A2A2;
                }
                .its-node-check-checked
                {
                    background-color: #00C7D2;
                }
                .its-node-name
                {
                    left: 124px;
                    height: 100%;
                    font-family: lato;
                    font-weight: 300;
                    font-size: 18px;
                    line-height: 100px;
                    display: inline-block;
                    position: absolute;
                }
                .its-node-input
                {
                    left: 300px;
                    top: 40px;
                    height: 20px;
                    width: 50%;
                    font-family: lato;
                    font-weight: 300;
                    font-size: 12px;
                    color: #000000;
                    border: none;
                    border-bottom: #00C7D2 1px solid;
                    background-color: #F2F2F2;
                    position: absolute;
                }
                .its-clickable-js
                {
                    cursor: pointer;
                }
            </style>
            <script>
                function checkselect(name)
                {
                    document.getElementById("check-" + name).classList.toggle("its-node-check-checked");
                }
                function apply()
                {
                    window.scrollTo(0, 0);
                    document.getElementById("infomsg").innerHTML = "Please wait...";
                    document.getElementById("info").classList.remove("its-invisible");
                    document.getElementById("error").classList.add("its-invisible");
                    if (document.getElementById("mysql_hostname").value == "")
                    {
                        window.scrollTo(0, 0);
                        document.getElementById("errormsg").innerHTML = "Please specify a MySQL hostname";
                        document.getElementById("error").classList.remove("its-invisible");
                        document.getElementById("info").classList.add("its-invisible");
                        return 0;
                    }
                    if (document.getElementById("mysql_port").value == "")
                    {
                        window.scrollTo(0, 0);
                        document.getElementById("errormsg").innerHTML = "Please specify a MySQL port";
                        document.getElementById("error").classList.remove("its-invisible");
                        document.getElementById("info").classList.add("its-invisible");
                        return 0;
                    }
                    if (document.getElementById("mysql_username").value == "")
                    {
                        window.scrollTo(0, 0);
                        document.getElementById("errormsg").innerHTML = "Please specify a MySQL username";
                        document.getElementById("error").classList.remove("its-invisible");
                        document.getElementById("info").classList.add("its-invisible");
                        return 0;
                    }
                    if (document.getElementById("mysql_password").value == "")
                    {
                        window.scrollTo(0, 0);
                        document.getElementById("errormsg").innerHTML = "Please specify a MySQL password";
                        document.getElementById("error").classList.remove("its-invisible");
                        document.getElementById("info").classList.add("its-invisible");
                        return 0;
                    }
                    if (document.getElementById("mysql_database").value == "")
                    {
                        window.scrollTo(0, 0);
                        document.getElementById("errormsg").innerHTML = "Please specify a MySQL database";
                        document.getElementById("error").classList.remove("its-invisible");
                        document.getElementById("info").classList.add("its-invisible");
                        return 0;
                    }
                    if (document.getElementById("system_username").value == "")
                    {
                        window.scrollTo(0, 0);
                        document.getElementById("errormsg").innerHTML = "Please specify a system username";
                        document.getElementById("error").classList.remove("its-invisible");
                        document.getElementById("info").classList.add("its-invisible");
                        return 0;
                    }
                    if (document.getElementById("system_password").value == "")
                    {
                        window.scrollTo(0, 0);
                        document.getElementById("errormsg").innerHTML = "Please specify a system password";
                        document.getElementById("error").classList.remove("its-invisible");
                        document.getElementById("info").classList.add("its-invisible");
                        return 0;
                    }
                    if (document.getElementById("general_name").value == "")
                    {
                        window.scrollTo(0, 0);
                        document.getElementById("errormsg").innerHTML = "Please specify a site name";
                        document.getElementById("error").classList.remove("its-invisible");
                        document.getElementById("info").classList.add("its-invisible");
                        return 0;
                    }
                    if (document.getElementById("general_title").value == "")
                    {
                        window.scrollTo(0, 0);
                        document.getElementById("errormsg").innerHTML = "Please specify a site title";
                        document.getElementById("error").classList.remove("its-invisible");
                        document.getElementById("info").classList.add("its-invisible");
                        return 0;
                    }
                    if (document.getElementById("system_password").value != document.getElementById("system_repeatpassword").value)
                    {
                        window.scrollTo(0, 0);
                        document.getElementById("errormsg").innerHTML = "System passwords do not match";
                        document.getElementById("error").classList.remove("its-invisible");
                        document.getElementById("info").classList.add("its-invisible");
                        return 0;
                    }
                    var http = new XMLHttpRequest();
                    var checks = Array.prototype.slice.call(document.querySelectorAll("*[id^=\"check-\"]"));
                    var extensions = "";
                    checks.forEach(function (check, idx)
                    {
                        if (check.classList.contains("its-node-check-checked"))
                        {
                            if (check.id.split("-", 2)[1] != undefined) extensions += "/" + encodeURIComponent(check.id.split("-", 2)[1]);
                        }
                    });
                    http.open("GET", "/apply/" + postv("mysql_hostname") + "/" + postv("mysql_port") + "/" + postv("mysql_username") + "/" + postv("mysql_password") + "/" + postv("mysql_database") + "/" + postv("mysql_prefix") + "/" + postv("system_username") + "/" + postv("system_password") + "/" + postv("general_name") + "/" + postv("general_title") + extensions, true);
                    http.onreadystatechange = function()
                    {
                        if (http.readyState == 4)
                        {
                            if (http.responseText.startsWith("OK "))
                            {
                                window.scrollTo(0, 0);
                                document.getElementById("infomsg").innerHTML = "Please wait...";
                                document.getElementById("info").classList.remove("its-invisible");
                                window.location = http.responseText.split(" ", 2)[1];
                            }
                            else
                            {
                                window.scrollTo(0, 0);
                                document.getElementById("errormsg").innerHTML = http.responseText;
                                document.getElementById("error").classList.remove("its-invisible");
                                document.getElementById("info").classList.add("its-invisible");
                            }
                        }
                    }
                    http.send();
                }
                function postv(id)
                {
                    return encodeURIComponent(document.getElementById(id).value);
                }
            </script>
        </head>
        <body>
            <div class="its-header-container">
                <div class="its-header-contents">
                    itais
                </div>
            </div>
            <div class="its-main-container">
                <div class="its-map">
                    <a class="its-map-link" href="/">
                        Installer
                    </a>
                    <span class="its-map-next">
                        →
                    </span>
                    <a class="its-map-link" href="/">
                        Home
                    </a>
                </span>
                <div class="its-node its-node-info its-invisible" id="info">
                    <div class="its-node-titlebox">
                        <span class="its-node-title its-node-title-notransform" id="infomsg">
                            
                        </span>
                    </div>
                </div>
                <div class="its-node its-node-error its-invisible" id="error">
                    <div class="its-node-titlebox">
                        <span class="its-node-title its-node-title-notransform" id="errormsg">
                            
                        </span>
                    </div>
                </div>
                <div class="its-node">
                    <div class="its-node-titlebox">
                        <span class="its-node-title">
                            Self test
                        </span>
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-checkbox-container">
                            <div class="its-node-checkbox-border">
                                <div class="its-node-checkbox">
                                    <div class="its-node-check<?php if (PHP_VERSION_ID > 70000) { ?> its-node-check-checked<?php } ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="its-node-name">
                            PHP 7 or more
                        </div>
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-checkbox-container">
                            <div class="its-node-checkbox-border">
                                <div class="its-node-checkbox">
                                    <div class="its-node-check<?php if (extension_loaded("mysqli")) { ?> its-node-check-checked<?php } ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="its-node-name">
                            mysqli extension
                        </div>
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-checkbox-container">
                            <div class="its-node-checkbox-border">
                                <div class="its-node-checkbox">
                                    <div class="its-node-check<?php if (extension_loaded("dom")) { ?> its-node-check-checked<?php } ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="its-node-name">
                            DOM extension
                        </div>
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-checkbox-container">
                            <div class="its-node-checkbox-border">
                                <div class="its-node-checkbox">
                                    <div class="its-node-check<?php if (is_writable($_SERVER['DOCUMENT_ROOT'])) { ?> its-node-check-checked<?php } ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="its-node-name">
                            Write permissions
                        </div>
                    </div>
                </div>
                <div class="its-node">
                    <div class="its-node-titlebox">
                        <span class="its-node-title">
                            MySQL details
                        </span>
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-name">
                            Hostname:
                        </div>
                        <input type="text" class="its-node-input" value="127.0.0.1" id="mysql_hostname">
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-name">
                            Port:
                        </div>
                        <input type="text" class="its-node-input" value="3306" id="mysql_port">
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-name">
                            Username:
                        </div>
                        <input type="text" class="its-node-input" value="" id="mysql_username">
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-name">
                            Password:
                        </div>
                        <input type="password" class="its-node-input" value="" id="mysql_password">
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-name">
                            Database:
                        </div>
                        <input type="text" class="its-node-input" value="" id="mysql_database">
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-name">
                            Table prefix:
                        </div>
                        <input type="text" class="its-node-input" value="itais_" id="mysql_prefix">
                    </div>
                </div>
                <div class="its-node">
                    <div class="its-node-titlebox">
                        <span class="its-node-title">
                            General information
                        </span>
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-name">
                            Site name:
                        </div>
                        <input type="text" class="its-node-input" value="itais community suite" id="general_name">
                        </input>
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-name">
                            Site title:
                        </div>
                        <input type="text" class="its-node-input" value="itais" id="general_title">
                        </input>
                    </div>
                </div>
                <div class="its-node">
                    <div class="its-node-titlebox">
                        <span class="its-node-title">
                            system account
                        </span>
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-name">
                            Username:
                        </div>
                        <input type="text" class="its-node-input" value="system" id="system_username">
                        </input>
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-name">
                            Password:
                        </div>
                        <input type="password" class="its-node-input" value="" id="system_password">
                        </input>
                    </div>
                    <div class="its-node-element">
                        <div class="its-node-name">
                            Confirm password:
                        </div>
                        <input type="password" class="its-node-input" value="" id="system_repeatpassword">
                        </input>
                    </div>
                </div>
            </div>
            <div class="its-node">
                <div class="its-node-titlebox">
                    <span class="its-node-title">
                        Extensions
                    </span>
                </div><?php
                $extensions = scandir("${INTERNAL}/install/extensions");
                foreach ($extensions as $efolder) if ($efolder != "." && $efolder != "..")
                {
                    ?>
                    <div class="its-node-element its-clickable-js" onclick="checkselect('<?php echo ($efolder); ?>')">
                        <div class="its-node-checkbox-container">
                            <div class="its-node-checkbox-border">
                                <div class="its-node-checkbox">
                                    <div class="its-node-check its-node-check-checked" id="check-<?php echo ($efolder); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="its-node-name">
                            <?php
                                $ename = str_replace("\n", "", file_get_contents("${INTERNAL}/install/extensions/${efolder}/uname.inf"));
                                echo($ename);
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="its-node its-node-button" onclick="apply()">
                <div class="its-node-titlebox">
                    <span class="its-node-title">
                        Continue
                    </span>
                </div>
            </div>
        </body>
    <?php
}
else
{
    switch ($REQARR[1])
    {
        case "apply":
        {
            if (count($REQARR) < 12)
            {
                echo("Request error: insufficient parameters");
                exit();
            }
            else
            {
                $mysqlhost = urldecode($REQARR[2]);
                $mysqlport = urldecode($REQARR[3]);
                $mysqluser = urldecode($REQARR[4]);
                $mysqlpass = urldecode($REQARR[5]);
                $mysqldb = urldecode($REQARR[6]);
                $systempass = urldecode($REQARR[9]);
                $extensions = [];
                foreach(array_slice($REQARR, 12) as $extension) $extensions[] = urldecode($extension);
                if ($mysqlic = mysqli_connect($mysqlhost, $mysqluser, $mysqlpass, $mysqldb, $mysqlport))
                {
                    $mysqlprefix = $mysqlic->escape_string(urldecode($REQARR[7]));
                    $systemuname = $mysqlic->escape_string(urldecode($REQARR[8]));
                    $systemsalt = $mysqlic->escape_string(bin2hex(random_bytes(32)));
                    $systemsession = bin2hex(random_bytes(128));
                    $sitename = $mysqlic->escape_string(urldecode($REQARR[10]));
                    $sitetitle = $mysqlic->escape_string(urldecode($REQARR[11]));
                    {
                        $result = $mysqlic->query("show tables like '${mysqlprefix}%'");
                        if ($result->num_rows > 0)
                        {
                            echo("itais is already installed on the database with the same prefix");
                            exit();
                        }
                    }
                    $config = fopen("${INTERNAL}/config.php", "w");
                    fwrite($config, '<?php' . "\n" . '$mysqlinfo = ["host" => "' . addslashes($mysqlhost) . '", "port" => "' . $mysqlport . '", "username" => "' . addslashes($mysqluser) . '", "password" => "' . addslashes($mysqlpass) . '", "database" => "' . addslashes($mysqldb) . '", "prefix" => "' . addslashes($mysqlprefix) . '"];' . "\n");
                    fclose($config);
                    $mysqlic->query("create table if not exists ${mysqlprefix}configuration (name text, value longtext)");
                    $mysqlic->query("create table if not exists ${mysqlprefix}users (uid integer auto_increment primary key, name text, salt varchar(64), password text)");
                    $mysqlic->query("create table if not exists ${mysqlprefix}sessions (sessionid varchar(256) primary key, user integer)");
                    $mysqlic->query("create table if not exists ${mysqlprefix}users_theme (uid integer primary key, theme text)");
                    $mysqlic->query("create table if not exists ${mysqlprefix}users_groups (uid integer, groupid integer)");
                    $mysqlic->query("create table if not exists ${mysqlprefix}users_primarygroup (uid integer primary key, groupid integer)");
                    $mysqlic->query("create table if not exists ${mysqlprefix}users_permissions (uid integer, name text, id integer, value bit(2))");
                    $mysqlic->query("create table if not exists ${mysqlprefix}users_avatar (uid integer, avatar text)");
                    $mysqlic->query("create table if not exists ${mysqlprefix}users_autobiography (uid integer, autobiography text)");
                    $mysqlic->query("create table if not exists ${mysqlprefix}groups (uid integer auto_increment primary key, name text, prename longtext, postname longtext, color tinytext)");
                    $mysqlic->query("create table if not exists ${mysqlprefix}groups_permissions (uid integer, name text, id integer, value bit(2))");
                    $mysqlic->query("create table if not exists ${mysqlprefix}isc (tag text, value longtext, extended bit(1))");
                    $mysqlic->query("insert into ${mysqlprefix}configuration values('name', '${sitename}')");
                    $mysqlic->query("insert into ${mysqlprefix}configuration values('title', '${sitetitle}')");
                    $mysqlic->query("insert into ${mysqlprefix}configuration values('defaultextens', '" . $mysqlic->escape_string($extensions[0]) . "')");
                    $mysqlic->query("insert into ${mysqlprefix}users (name, salt, password) values('Unregistered', '', '')");
                    $mysqlic->query("insert into ${mysqlprefix}users (name, salt, password) values('${systemuname}', '" . $systemsalt . "', '" . $mysqlic->escape_string(hash("whirlpool", "${systemsalt}${systempass}")) . "')");
                    $mysqlic->query("insert into ${mysqlprefix}groups (name, prename, postname, color) values('System administrator', '" . $mysqlic->escape_string('<span style="color: #007700">') . "', '" . $mysqlic->escape_string('</span>') . "', '#00A2CE')");
                    $mysqlic->query("insert into ${mysqlprefix}groups (name, prename, postname, color) values('Registered', '', '', null)");
                    $mysqlic->query("insert into ${mysqlprefix}groups (name, prename, postname, color) values('Guest', '', '', null)");
                    $mysqlic->query("insert into ${mysqlprefix}sessions values('" . $mysqlic->escape_string($systemsession) . "', 2)");
                    $mysqlic->query("insert into ${mysqlprefix}users_theme values(1, 'itenial')");
                    $mysqlic->query("insert into ${mysqlprefix}users_avatar values(1, '/theme/img/avatar.png')");
                    $mysqlic->query("insert into ${mysqlprefix}users_groups values(1, 3)");
                    $mysqlic->query("insert into ${mysqlprefix}users_groups values(2, 1)");
                    $mysqlic->query("insert into ${mysqlprefix}users_groups values(2, 2)");
                    $mysqlic->query("insert into ${mysqlprefix}users_primarygroup values(2, 1)");
                    $mysqlic->query("insert into ${mysqlprefix}groups_permissions values(1, 'administration-panel', -1, 1)");
                    $mysqlic->close();
                    setcookie("session", $systemsession, time() + 373248000, "/", $SNAME, false, true);
                    echo("OK /native/extensions/autoinstall/" . implode("/", $extensions));
                    exit();
                }
                else
                {
                    echo("Failed to connect to MySQL: " . mysqli_connect_error());
                    exit();
                }
            }
        }
        break;
        default:
        {
            echo("Request error: not found"); exit();
        } break;
    }
}