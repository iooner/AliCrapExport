<?php
include('./simple_html_dom.php');
$max_file_size = 5*1024*1024; //5MB
$uniquser = uniqid();
$path = "uploads/".$uniquser;
$count = 0;

$valid_formats = array("htm","html");

$valid_formats_server = array(
    "text/html",
);

//prevent uploading from wrong file types(server secure)
foreach ($_FILES['files']['type'] as $t => $tName) {
    if(!in_array($_FILES['files']['type'][$t], $valid_formats_server)){
        echo "wrong FILE TYPE";
        return;
    }
}
// Create CSV file
$fichier = fopen('uploads/out.csv', 'c+b');
fwrite($fichier, "Vendeur;Article;Lien;Prix;Total;Date;Image\r\n");
foreach ($_FILES['files']['name'] as $f => $name) {

    if ($_FILES['files']['error'][$f] == 4) {
        continue; // Skip file if any error found
    }
    if ($_FILES['files']['error'][$f] == 0) {
        if ($_FILES['files']['size'][$f] > $max_file_size) {
            echo $message[] = "$name is too large!";
            continue; // Skip large files
        }
        elseif(!in_array(pathinfo($name, PATHINFO_EXTENSION), $valid_formats)){
            echo $message[] = "$name is not a valid format";
            continue; // Skip invalid file formats
        }
        else{ // No error found! Move uploaded files
            //if(move_uploaded_file($_FILES["files"]["tmp_name"][$f], $path.$name))
            move_uploaded_file($_FILES["files"]["tmp_name"][$f], $path.".html");
            $count++;



            $html = file_get_html('uploads/'.$uniquser.'.html');
            foreach($html->find('tbody.order-item-wraper') as $article) {
                $item['store-info'] = $article->find('td.store-info span.info-body', 0)->plaintext;
                $item['item-name'] = $article->find('tr.order-body a.baobei-name', 0)->plaintext;
                $item['item-link'] = $article->find('tr.order-body td.product-sets div.product-left a.pic.s50', 0)->href;
                $item['item-totalprice'] = trim(str_replace('€', '', $article->find('tr.order-head p.amount-num', 0)->plaintext));
                $item['item-price'] = trim(str_replace('€', '', $article->find('tr.order-body td.product-sets div.product-right p.product-amount span', 0)->plaintext));
                $item['item-orderdate'] = trim($article->find('tr.order-head p.second-row span.info-body', 0)->plaintext);

                $inimg = array(str_replace(".html", "", $name),"./","_files/",".jpg_50x50.jpg");
                $outimg   = array("","","",".jpg");
                $item['item-picture'] = "https://ae01.alicdn.com/kf/".str_replace($inimg, $outimg, $article->find('tr.order-body a.pic.s50 img', 0)->src);

                //$item['item-fee'] = $item['item-totalprice'] % $item['item-price'];
                fwrite($fichier, $item['store-info'].";".$item['item-name'].";".$item['item-link'].";".$item['item-price'].";".$item['item-totalprice'].";".$item['item-orderdate'].";".$item['item-picture']."\r\n");
                $articles[] = $item;
            }
            echo "<pre>";
            var_dump($articles);
        }
    }
}