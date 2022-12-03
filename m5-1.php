<?php

$pdo = new PDO(
    'mysql:host=hostName;dbname=datebaseName;charset=UTF8',
    'userName',
    'password',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)
);

$stmt = $pdo->query("
    CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT,
        name CHAR(32),
        comment TEXT,
        password CHAR(32),
        datetime DATETIME,
        PRIMARY KEY(id)
    );
");
    
//=====================
//デバッグ用、テーブル確認
//=====================
// $stmt = $pdo->query("SHOW TABLES");

// foreach ($stmt as $row) {
//     echo $row[0] . "<br>";
// }


//====================
//====投稿フォーム====
//====================
if(!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["pass1"])) {
    $name = $_POST["name"];
    $comment = $_POST["comment"];
    $pass1 = $_POST["pass1"];
    
    if (empty($_POST["confirmNum"])) {
        //===PDO===
        $stmt = $pdo->prepare("
            INSERT INTO posts (name, comment, password, datetime)
            VALUES (:name, :comment, :pass1, NOW());
        ");
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        $stmt->bindValue(":comment", $comment, PDO::PARAM_STR);
        $stmt->bindValue(":pass1", $pass1, PDO::PARAM_STR);
        $stmt->execute();
    }
    
    //編集機能　confirmNumがあるときとない時でわけないと編集だけでなく新たな投稿もされてしまう。
    elseif (!empty($_POST["confirmNum"])) {
        $confirmNum = $_POST["confirmNum"];
        
        $stmt = $pdo->prepare("
            UPDATE posts
            SET name = :name, comment = :comment, datetime = NOW()
            WHERE id = :confirmNum; 
        ");
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        $stmt->bindValue(":comment", $comment, PDO::PARAM_STR);
        $stmt->bindValue(":confirmNum", $confirmNum, PDO::PARAM_STR);
        $stmt->execute();
    }
}


//====================
//====削除フォーム====
//====================
if(!empty($_POST["deleteNum"]) && !empty($_POST["pass2"])) {
    $deleteNum = $_POST["deleteNum"];
    $pass2 = $_POST["pass2"];
    
    // $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :deleteNum AND password = :pass2");
    // $stmt->bindValue(":deleteNum", $deleteNum, PDO::PARAM_INT);
    // $stmt->bindValue(":pass2", $pass2, PDO::PARAM_STR);
    // $stmt->execute();
    
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :deleteNum");
    $stmt->bindValue(":deleteNum", $deleteNum, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll();
    
    foreach($result as $row) {
        $pass = $row["password"];
        if ($pass == $pass2) {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :deleteNum AND password = :pass2");
        $stmt->bindValue(":deleteNum", $deleteNum, PDO::PARAM_INT);
        $stmt->bindValue(":pass2", $pass2, PDO::PARAM_STR);
        $stmt->execute();
        }
    
        elseif($pass != $pass2) {
            $errorMessage ="正しいパスワードを入力してください。";
        }
    }
}


//====================
//====編集フォーム====
//====================
if(!empty($_POST["editNum"]) && !empty($_POST["pass3"])) {
    $editNum = $_POST["editNum"];
    $pass3 = $_POST["pass3"];
    
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :editNum AND password = :pass3");
    $stmt->bindValue(":editNum", $editNum, PDO::PARAM_INT);
    $stmt->bindValue(":pass3", $pass3, PDO::PARAM_STR);
    $stmt->execute();
    $lists = $stmt->fetchAll();
    
    foreach ($lists as $list) {
        $editName = $list["name"];
        $editComment = $list["comment"];
        $id = $list["id"];
        $password = $list["password"];
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission5-1</title>
</head>
<body>
    【投稿フォーム】
    <form method="post">
        <input
            type="text" name="name" placeholder="名前"
            value=
            "<?php if (isset($editName)) {
                echo $editName;
            }
        ?>">
        <br>
        <input
            type="text" name="comment" placeholder="コメント"
            value=
            "<?php
            if (isset($editComment)) {
                echo $editComment;
            }
        ?>">
        <br>
        <input
            type="password" name="pass1" placeholder="パスワード"
            value=
            "<?php
            if (isset($password)) {
                echo $password;
            }
        ?>">
        
        <input
            type="hidden" name="confirmNum"
            value=
            "<?php
            if (isset($id)) {
                echo $id;
            }
        ?>">
        <input type="submit" value="送信">
    </form>
    <br>
    
    【削除フォーム】
    <form method="post">
        <input type="text" name="deleteNum" placeholder="削除番号">
        <br>
        <input type="password" name="pass2" placeholder="パスワード">
        <input type="submit" value="削除">
    </form>
    <br>
    
    【編集フォーム】
    <form method="post">
        <input type="text" name="editNum" placeholder="編集番号">
        <br>
        <input type="password" name="pass3" placeholder="パスワード">
        <input type="submit" value="編集">
    </form>
    <h3>行ってみたい国は？？</h3>
    ----投稿履歴----
    <br>
</body>
</html>

<?php

//エラーメッセージ表示機能

//--投稿フォームで名前が記入されていないとき
if (empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["pass1"])){
    $errorMessage = "名前を入力してください。";
}

//--投稿フォームでコメントが記入されていないとき--
elseif(!empty($_POST["name"]) && empty($_POST["comment"]) && !empty($_POST["pass1"])){
    $errorMessage = "コメントを入力してください。";
}

//--投稿フォームで名前とコメントが記入されていないとき--
elseif(empty($_POST["name"]) && empty($_POST["comment"]) && !empty($_POST["pass1"])){
    $errorMessage = "名前とコメントを入力してください。";
}

//--投稿フォームで名前とパスワードが記入されていないとき--
elseif(empty($_POST["name"]) && !empty($_POST["comment"]) && empty($_POST["pass1"])){
    $errorMessage = "名前とパスワードを入力してください。";
}

//--投稿フォームでコメントとパスワードが記入されていないとき--
elseif(!empty($_POST["name"]) && empty($_POST["comment"]) && empty($_POST["pass1"])){
    $errorMessage = "コメントとパスワードを入力してください。";
}

//--削除番号が記入されていないとき--
elseif(empty($_POST["deleteNum"]) && !empty($_POST["pass2"])) {
    $errorMessage = "削除する番号を入力してください。";
}

//--編集番号が記入されていないとき--
elseif(empty($_POST["editNum"]) && !empty($_POST["pass3"])) {
    $errorMessage = "編集する番号を入力してください。";
}

//--パスワード未記入のとき--
elseif(!empty($_POST["name"]) && !empty($_POST["comment"]) && empty($_POST["pass1"])) {
    $errorMessage = "パスワードを記入してください";
}

elseif (!empty($_POST["deleteNum"]) && empty($_POST["pass2"])) {
    $errorMessage = "パスワードを記入してください。";
}

elseif (!empty($_POST["editNum"]) && empty($_POST["pass3"])) {
    $errorMessage = "パスワードを記入してください。";
}


if (isset($errorMessage)) {
    echo "------Error message------<br>";
    echo "$errorMessage <br>";
    echo "----------------------------<br>";
}

//投稿履歴表示機能

$stmt = $pdo->query("SELECT * FROM posts");
foreach ($stmt as $row) {
    echo $row["id"] . " ";
    echo $row["name"] . " ";
    echo $row["comment"] . " ";
    echo $row["datetime"] . "<br>";
    echo "<hr>";
}


//↓テーブル削除
// $stmt = $pdo->query("DROP TABLE posts");

?>

