<?php

    if ($_SERVER["REQUEST_METHOD"]=="GET") {
        if (isset($_GET["count"])) {
            $count = query_count();
            if (!$count) {
                http_response_code(500);
            } else {
                echo($count);
            }
            return;
        }
        
        $page = $_GET["page"];
        $posts = query_posts($page);
        if (!$posts) {
            http_response_code(500);
        } else {
            echo($posts);
        }
        return;
    } elseif ($_SERVER["REQUEST_METHOD"]=="POST") {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data["content"]) || !isset($data["password"])) {
            http_response_code(400);
        }

        $rtn = create_post($data["content"], $data["password"]);
        if ($rtn==-1) {
            http_response_code(401);
        } elseif ($rtn==0) {
            http_response_code(500);
        }
        return;
    } elseif ($_SERVER["REQUEST_METHOD"]=="PUT") {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data["content"]) || !isset($data["password"]) || !isset($data["id"])) {
            http_response_code(400);
        }

        $rtn = update_post($data["id"], $data["content"], $data["password"]);
        if ($rtn==-1) {
            http_response_code(401);
        } elseif ($rtn==0) {
            http_response_code(500);
        }
        return;
    } elseif ($_SERVER["REQUEST_METHOD"]=="DELETE") {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data["id"]) || !isset($data["password"])) {
            http_response_code(400);
        }

        $rtn = delete_post($data["id"], $data["password"]);
        if ($rtn==-1) {
            http_response_code(401);
        } elseif ($rtn==0) {
            http_response_code(500);
        }
        return;
    }

    function delete_post($id, $pass)
    {
        $config = json_decode(file_get_contents("config.json"), true);
        if ($config["password"]!=$pass) {
            return -1;
        }

        $sqlcmd = "DELETE FROM {table_name} WHERE ObjectId=$id";
        $result = exec_sql($sqlcmd);
        if (!$result) {
            return 0;
        }

        return 1;
    }

    function update_post($id, $content, $pass)
    {
        $config = json_decode(file_get_contents("config.json"), true);
        if ($config["password"]!=$pass) {
            return -1;
        }

        $content = replace_escape($content);
        $sqlcmd = "UPDATE {table_name} SET Content = '$content' WHERE ObjectId=$id";
        $result = exec_sql($sqlcmd);
        if (!$result) {
            return 0;
        }

        return 1;
    }

    function create_post($content, $pass)
    {
        $config = json_decode(file_get_contents("config.json"), true);
        if ($config["password"]!=$pass) {
            return -1;
        }

        $content = replace_escape($content);
        $sqlcmd = "INSERT INTO {table_name} (Content) VALUES ('$content')";
        $result = exec_sql($sqlcmd);
        if (!$result) {
            return 0;
        }

        return 1;
    }

    function replace_escape($content)
    {
        $config = json_decode(file_get_contents("config.json"), true);
        $dbuser = $config["dbUser"];
        $dbhost = $config["dbHost"];
        $dbpass = $config["dbPass"];
        $dbtable = $config["dbTable"];
        $dbname = $config["dbName"];

        $mysqli = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        if (!$mysqli) {
            return null;
        }
        $content =  mysqli_real_escape_string($mysqli, $content);
        mysqli_close($mysqli);

        return $content;
    }

    function exec_sql($sqlcmd)
    {
        $config = json_decode(file_get_contents("config.json"), true);
        $dbuser = $config["dbUser"];
        $dbhost = $config["dbHost"];
        $dbpass = $config["dbPass"];
        $dbtable = $config["dbTable"];
        $dbname = $config["dbName"];

        $mysqli = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        if (!$mysqli) {
            return null;
        }
        $mysqli->set_charset("utf8");
        if (!table_exist_or_create($dbtable, $dbname, $mysqli)) {
            return null;
        }

        $sqlcmd = str_replace("{table_name}", $dbtable, $sqlcmd);
        
        $result = $mysqli->query($sqlcmd);

        mysqli_close($mysqli);

        return $result;
    }

    function query_posts($page)
    {
        $config = json_decode(file_get_contents("config.json"), true);
        $post_each_page = $config["postOnPage"];
        $page_offset = $post_each_page*($page-1);
        $query_sql = "SELECT * FROM {table_name} ORDER BY CreatedAt DESC LIMIT $post_each_page OFFSET $page_offset";
        $result = exec_sql($query_sql);
        
        if (!$result) {
            return null;
        }

        $arr = array();
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($arr, $row);
        }
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    function query_count()
    {
        $query_sql = "SELECT COUNT(*) FROM {table_name}";
        $result = exec_sql($query_sql);

        if (!$result) {
            return null;
        }

        $count = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $count = intval($row["COUNT(*)"]);
        }
        return "{count:$count}";
    }

    function table_exist_or_create($tablename, $dbname, $mysqli)
    {
        $sql = "SELECT * FROM information_schema.TABLES WHERE table_schema='$dbname' AND table_name='$tablename'";
        $result = $mysqli->query($sql);
        if ($result->num_rows==0) {
            $sql = "
            CREATE TABLE `$dbname`.`$tablename` (
            `ObjectId` int NOT NULL AUTO_INCREMENT,
            `Content` text NOT NULL,
            `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`ObjectId`));
            ";
            return $mysqli->query($sql);
        } else {
            return true;
        }
    }
