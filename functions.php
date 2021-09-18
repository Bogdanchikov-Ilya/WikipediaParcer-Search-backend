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
    $searchValue = $data['text'];
    $searchValue= mb_strtolower($searchValue);

    //получаю id введенного слова
    $wordsId = mysqli_query($connect, "SELECT `id` FROM `words` WHERE `text` = '$searchValue'");
    $wordsId = mysqli_fetch_assoc($wordsId);

    var_dump($wordsId);
    // пушу в результирующую таблицу айти слова

    mysqli_query($connect, "INSERT INTO `articles_words` (`articles_id`, `words_id`, `counter`) VALUES (10, 10, 10)");
    // получаю массив айдишников статей со словом $searchValue
    $articleIdArr = mysqli_query($connect, "SELECT `id` FROM `articles` WHERE `body` LIKE '%$searchValue%'");
    $articleIdArr = mysqli_fetch_array($articleIdArr);
    // пушу массив айдишников в связующую таблицу
    mysqli_query($connect, "INSERT INTO `articles_words` (`articles_id`) VALUES ('$articleIdArr')");
    // делаю запрос с каждым из айдишников и беру текст боди
    $items = [];
    foreach ($articleIdArr as $value) {
        $items[] = mysqli_query($connect, "SELECT `body` FROM `articles` WHERE `id` = '$value'");

    }
    var_dump($items);
    function getCount($text, $searchValue) {
        return substr_count($text, $searchValue);
    }
    $textCountArray = [];
    for ($i = 0; $i < count($items); $i++){
        $textCountArray[$i] = getCount($items[$i], $searchValue);
    };
    // пушу массив с кол-вом вхождений в результирующую таблицу
    mysqli_query($connect, "INSERT INTO `articles_words` (`counter`) VALUES ('$textCountArray')");
    $res = mysqli_query($connect, "SELECT * FROM `articles_words`");


//    echo json_encode($res);
}