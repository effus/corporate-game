<?php

if ($_POST['host']) {
    $config = [
        'db' => [
            'host' => $_POST['host'],
            'database' => $_POST['database'],
            'user' => $_POST['user'],
            'password' => $_POST['password'],
        ],
        'domain' => $_POST['domain'],
        'admin' => [
            'hash' => md5($_POST['adm_name'] . $_POST['adm_pass'])
        ]
    ];

    $db = $config['db'];
    try {
        $connection = new PDO('mysql:host=' . $db['host'].';dbname=' . $db['database'], $db['user'], $db['password']);
    } catch (Exception $e) {
        die('Connection failed ' . $e->getMessage());
    }
    
    $dump = file_get_contents(__DIR__ . '/data/db.sql');
    
    $queries = explode('---', $dump);

function execQuery($query, $connection) {
    try {
        $stmt = $connection->prepare($query);
        if (!$stmt->execute()) {
            throw new Exception('Fail execute dump');
        }
    } catch (Exception $e) {
        die('SQL error: ' . $e->getMessage());
    }
}

$c=0;
    foreach($queries as $query) {
        execQuery($query, $connection);
        $c++;
    }

    echo 'Queries executed: ' . $c . '<br />';

    if (is_writeable(__DIR__ . '/data')) {
        file_put_contents(__DIR__ . '/data/config.json', json_encode($config));
        echo '<b>Config stored!</b>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Setup</title>
</head>
<body>
    <form method="POST">

<table>
<tr>
    <td colspan="2">Database</td>
</tr>
<tr>
    <td>Host</td>
    <td><input type="text" name="host" id="host"></td>
</tr>
<tr>
    <td>Database</td>
    <td><input type="text" name="database" id="database"></td>
</tr>
<tr>
    <td>User</td>
    <td><input type="text" name="user" id="user"></td>
</tr>
<tr>
    <td>Password</td>
    <td><input type="text" name="password" id="password"></td>
</tr>
<tr>
    <td colspan="2">Other</td>
</tr>
<tr>
    <td>Domain</td>
    <td><input type="text" name="domain" id="domain"></td>
</tr>
<tr>
    <td>Admin name</td>
    <td><input type="text" name="adm_name" id="adm_name"></td>
</tr>
<tr>
    <td>Admin password</td>
    <td><input type="text" name="adm_pass" id=adm_pass></td>
</tr>
</table>
<button type="submit">Submit</button>   
    </form>
</body>
</html>