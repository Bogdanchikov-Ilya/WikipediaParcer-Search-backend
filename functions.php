<?php
function getArticles($connect) {
    $articles = mysqli_query($connect, "SELECT * FROM `articles`");
    $articlesList = [];
    while ($article = mysqli_fetch_assoc($articles)){
        $articlesList[] = $article;
    }
    echo json_encode($articlesList);
}


function addArticles($connect, $data) {

    $title = $data['title'];
    $body = $data['body'];

    $body = mb_strimwidth($body, 0, 50000, "..."); // обрезаю т.к если много символов в бд не добавляется
    $body = str_replace(array("?","!",",",";",".","-","(",")","—"), "", $body);
    $body = preg_replace('/\s+/', ' ', $body);
    $body = mb_strtolower($body);

    $url = $data['url'];
    $size = $data['size'];
    $count_words = $data['count_words'];

    mysqli_query($connect, "INSERT INTO `articles` (`title`, `body`, `url`, `size`, `count_words`) VALUES ('$title', '$body', '$url', '$size', '$count_words')");

    $body=explode(' ',$body);

    foreach($body as $value) {
        $count = mysqli_query($connect, "SELECT * FROM `words` WHERE `text` = '$value'");
        if(mysqli_num_rows($count) == 0) {
            mysqli_query($connect, "INSERT INTO `words` (`text`) VALUES ('$value')");
        }
    }
    http_response_code(201);
    $res = [
        "status" => true,
        "post_id" => mysqli_insert_id($connect)
    ];
    echo json_encode($res);
}

function search($connect, $data) {
    // обработка введенного слова
    $searchValue = $data['text'];
    $searchValue= mb_strtolower($searchValue);


    //получаю id введенного слова
    $wordsId = mysqli_query($connect, "SELECT `id` FROM `words` WHERE `text` = '$searchValue'");
    $wordsId = mysqli_fetch_all($wordsId, MYSQLI_ASSOC);
    foreach ($wordsId as $row) {
        $wordsId = $row["id"];
    }
    $wordsId = intval($wordsId);

    // пушу в результирующую таблицу айди слова
    // mysqli_query($connect, "INSERT INTO `articles_words` (`words_id`, `counter`) VALUES ('$wordsId', NULL)");

    // получаю массив айдишников статей со словом $searchValue
    $getArticleId = mysqli_query($connect, "SELECT `id` FROM `articles` WHERE `body` LIKE '%в%'");
    $articleIdArray = [];
    while ($row = mysqli_fetch_assoc($getArticleId)) {
        $articleIdArray[] = intval($row["id"]);
    }
    $articleIdArray = json_encode($articleIdArray);


    // пушу массив айдишников в связующую таблицу
    // mysqli_query($connect, "INSERT INTO `articles_words` (`articles_id`) VALUES ('$articleIdArray')");


    // делаю запрос с каждым из айдишников и беру текст боди
    $textArticlesArray = [];
    $articleIdArray = json_decode($articleIdArray);
    foreach ($articleIdArray as $value) {
        $item = mysqli_query($connect, "SELECT `body` FROM `articles` WHERE `id` = '$value'");
        $item = mysqli_fetch_assoc($item);
        $textArticlesArray[] = $item;
    }
    $articleIdArray = json_encode($articleIdArray);


    // подсчитываю кол-во повторений слова в каждом эелемнте массива $textArticlesArray
    $counterArray = [];
    foreach ($textArticlesArray as $row) {
        $counterArray[] = substr_count($row["body"], $searchValue);
    }
    $counterArray = json_encode($counterArray);

    //пушу массив с счтчиками в связующую таблицу
    mysqli_query($connect, "INSERT INTO `articles_words` (`articles_id`, `words_id`, `counter`) VALUES ('$articleIdArray', '$wordsId', '$counterArray')");
//    print_r($articleIdArray);
//    print_r($wordsId);
//    print_r($counterArray);

    $articleIdArray = json_decode($articleIdArray);
    $counterArray = json_decode($counterArray);
    $titlesArray = [];
    $res = [];



    for ($i = 0; $i < count($articleIdArray); $i++) {

        $title = mysqli_query($connect, "SELECT `title` FROM `articles` WHERE `id` = $articleIdArray[$i]");
        $titlesArray[] = mysqli_fetch_assoc($title);

        // SELECT `counter` FROM `articles_id` WHERE `counter` = $counterArray[$is]

        $res[] = ["title" => $titlesArray[$i],
                    "counter" => $counterArray[$i]];

    }
    echo json_encode($res);
    die();

    // беру название статиь и количество совпадений и отдаю на фронт

}