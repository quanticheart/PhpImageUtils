<?php
$pok1 = new mysqli('localhost', 'root', '', 'pok_t') or die("Error " . mysqli_error($pok1));
$pok2 = new mysqli('localhost', 'root', '', 'poketudo') or die("Error " . mysqli_error($pok2));

$mostraDados = mysqli_query($pok2, "SELECT * FROM monsters") or die (mysqli_error($mostraDados));

while ($linha = mysqli_fetch_array($mostraDados)) {
    $id = $linha["id"];
    $num = $linha["num"];
    $fotoid = $linha["fotoid"];
    $url_pok = $linha["url_pok"];
    $url_pok2 = "pokemon-$url_pok";
    $name = $linha["name"];
    $description = $linha["description"];
    $ability1ID = $linha["abilityid1"];
    $ability2ID = $linha["abilityid2"];
    $ability3ID = $linha["abilityid3"];

    $ab1 = mysqli_query($pok2, "SELECT * FROM abilities WHERE id = '" . $ability1ID . "'") or die (mysqli_error($ab1));
    $linha = mysqli_fetch_array($ab1);
    $nome1 = $linha["name"];

    $ab2 = mysqli_query($pok2, "SELECT * FROM abilities WHERE id = '" . $ability2ID . "'") or die (mysqli_error($ab2));
    $linha = mysqli_fetch_array($ab2);
    $nome2 = $linha["name"];

    $ab3 = mysqli_query($pok2, "SELECT * FROM abilities WHERE id = '" . $ability3ID . "'") or die (mysqli_error($ab3));
    $linha = mysqli_fetch_array($ab3);
    $nome3 = $linha["name"];

    $max = 250;

    $descricaonova = substr_replace($description, (strlen($description) > $max ? '...' : ''), $max);

    $ky = "Pokemon, habilidades,  habilidade,  abilities,  ability, golpes, moves, tm, breeding, evs, status, poketudo, sun, moon, detonado, dicas, Nintendo, 3ds, ivs, reprodução, evolução, evoluções, ";
    $titilenome = "" . $num . "# $name";
    $keynova = "" . $name . ", " . $nome1 . ", " . $nome2 . ", " . $nome3 . ", " . $ky . "";

    $insereDados = mysqli_query($pok1, "INSERT INTO url (id, link, title, descricao, keywords, imgog, nivel) VALUES ('', '$url_pok2', '$titilenome', '$descricaonova', '$keynova', '$fotoid', '2')") or die (mysqli_error());

    if ($insereDados == true) {
        echo "update $name <br>";
    } else {
        echo " FIM ";
    }
}

$mostraDados = mysqli_query($pok2, "SELECT * FROM abilities") or die (mysqli_error($mostraDados));

while ($linha = mysqli_fetch_array($mostraDados)) {
    $id = $linha["id"];
    $fotoid = "habilitie";
    $url_pok = $linha["url"];
    $url_pok2 = "habilidade-$url_pok";
    $name = $linha["name"];
    $description = $linha["effect"];

    $max = 250;

    $descricaonova = substr_replace($description, (strlen($description) > $max ? '...' : ''), $max);

    $ky = "Pokemon, habilidades, habilidade, abilidades, golpes, moves, tm, breeding, evs, status, poketudo, sun, moon, detonado, dicas, Nintendo, 3ds, ivs, reprodução, evolução, evoluções, ";
    $titilenome = "Habilidade $name";
    $keynova = "" . $name . ", " . $ky . "";

    $insereDados = mysqli_query($pok1, "INSERT INTO url (id, link, title, descricao, keywords, imgog, nivel) VALUES ('', '$url_pok2', '$titilenome', '$descricaonova', '$keynova', '$fotoid', '2')") or die (mysqli_error());

    if ($insereDados == true) {
        echo "update $name <br>";
    } else {
        echo " FIM ";
    }
}

$mostraDados = mysqli_query($pok2, "SELECT * FROM monstersalt") or die (mysqli_error($mostraDados));

while ($linha = mysqli_fetch_array($mostraDados)) {
    $id = $linha["id"];
    $num = $linha["num"];
    $fotoid = $linha["fotoid"];
    $url_pok = $linha["url_pok"];
    $url_pok2 = "alternativo-$url_pok";
    $name = $linha["name"];
    $namef = $linha["forma_name"];
    $description = $linha["description"];
    $ability1ID = $linha["abilityid1"];
    $ability2ID = $linha["abilityid2"];
    $ability3ID = $linha["abilityid3"];

    $ab1 = mysqli_query($pok2, "SELECT * FROM abilities WHERE id = '" . $ability1ID . "'") or die (mysqli_error($ab1));
    $linha = mysqli_fetch_array($ab1);
    $nome1 = $linha["name"];

    $ab2 = mysqli_query($pok2, "SELECT * FROM abilities WHERE id = '" . $ability2ID . "'") or die (mysqli_error($ab2));
    $linha = mysqli_fetch_array($ab2);
    $nome2 = $linha["name"];

    $ab3 = mysqli_query($pok2, "SELECT * FROM abilities WHERE id = '" . $ability3ID . "'") or die (mysqli_error($ab3));
    $linha = mysqli_fetch_array($ab3);
    $nome3 = $linha["name"];

    $max = 250;

    $descricaonova = substr_replace($description, (strlen($description) > $max ? '...' : ''), $max);

    $ky = "Pokemon, habilidades,  habilidade,  abilities,  ability, golpes, moves, tm, breeding, evs, status, poketudo, sun, moon, detonado, dicas, Nintendo, 3ds, ivs, reprodução, evolução, evoluções, ";
    $titilenome = "" . $num . "# $namef";
    $keynova = "" . $name . ", " . $nome1 . ", " . $nome2 . ", " . $nome3 . ", " . $ky . "";

    $insereDados = mysqli_query($pok1, "INSERT INTO url (id, link, title, descricao, keywords, imgog, nivel) VALUES ('', '$url_pok2', '$titilenome', '$descricaonova', '$keynova', '$fotoid', '2')") or die (mysqli_error());

    if ($insereDados == true) {
        echo "update $name <br>";
    } else {
        echo " FIM ";
    }
}

$mostraDados = mysqli_query($pok2, "SELECT * FROM items ") or die (mysqli_error($mostraDados));

while ($linha = mysqli_fetch_array($mostraDados)) {
    $id = $linha["id"];
    $fotoid = $linha["identifier"];
    $url_pok = $fotoid;
    $url_pok2 = "item-$url_pok";
    $name = $linha["name"];

    $mostraDados2 = mysqli_query($pok2, "SELECT * FROM item_flavor_text WHERE item_id = '" . $id . "' AND version_group_id = '17' AND language_id = '9' ") or die (mysqli_error());
    $linha = mysqli_fetch_array($mostraDados2);
    $description = $linha["flavor_text"];

    $max = 250;

    $descricaonova = substr_replace($description, (strlen($description) > $max ? '...' : ''), $max);

    $ky = "Pokemon, drop, chance, item, items, abilidades, golpes, moves, tm, breeding, evs, status, poketudo, sun, moon, detonado, dicas, Nintendo, 3ds, ivs, reprodução, evolução, evoluções, ";
    $titilenome = "Item $name";
    $keynova = "" . $name . ", " . $ky . "";

    $insereDados = mysqli_query($pok1, "INSERT INTO url (id, link, title, descricao, keywords, imgog, nivel) VALUES ('', '$url_pok2', '$titilenome', '$descricaonova', '$keynova', '$fotoid', '2')") or die (mysqli_error());

    if ($insereDados == true) {
        echo "update $name <br>";
    } else {
        echo " FIM ";
    }
}

$mostraDados = mysqli_query($pok2, "SELECT * FROM moves") or die (mysqli_error($mostraDados));

while ($linha = mysqli_fetch_array($mostraDados)) {
    $id = $linha["id"];

    $idfoto = $linha["type_id"];
    $fotoid = "tm-$idfoto";
    $url_pok = $linha["identifier"];
    $url_pok2 = "golpe-$url_pok";
    $name = $linha["name"];

    $query2 = mysqli_query($pok2, "SELECT * FROM tipo WHERE id = '" . $fotoid . "' ") or die (mysqli_error());
    $linha2 = mysqli_fetch_array($query2);
    $tipo = $linha2["tipo"];

    $mostraDados2 = mysqli_query($pok2, "SELECT * FROM move_flavor_text WHERE move_id = '" . $id . "' AND version_group_id = '17' AND language_id = '9'  ") or die (mysqli_error());
    $row = mysqli_fetch_array($mostraDados2);
    $description = $row["flavor_text"];

    $max = 250;

    $descricaonova = substr_replace($description, (strlen($description) > $max ? '...' : ''), $max);

    $ky = "Pokemon, move, golpe, golpe tipo $tipo, golpes tipo $tipo, tm, hm, abilidades, golpes, moves, tm, breeding, evs, status, poketudo, sun, moon, detonado, dicas, Nintendo, 3ds, ivs, reprodução, evolução, evoluções, ";
    $titilenome = "Golpe $name";
    $keynova = "" . $name . ", " . $ky . "";

    $insereDados = mysqli_query($pok1, "INSERT INTO url (id, link, title, descricao, keywords, imgog, nivel) VALUES ('', '$url_pok2', '$titilenome', '$descricaonova', '$keynova', '$fotoid', '2')") or die (mysqli_error());


    if ($insereDados == true) {
        echo "update $name <br>";
    } else {
        echo " FIM ";
    }
}

$fp = fopen("sitemap.xml", "a");

fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" 
  xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">');

$mostraDados = mysqli_query($pok1, "SELECT * FROM url") or die (mysqli_error($mostraDados));

while ($linha = mysqli_fetch_array($mostraDados)) {
    $link = $linha["link"];
    $imgog = $linha["imgog"];
    $title = $linha["title"];

    fwrite($fp, '
    <url> 
    <loc>http://www.poketudo.com/' . $link . '</loc> 
    <image:image>
       <image:loc>http://www.poketudo.com/app/pokedex/files/images/og/' . $imgog . '.png</image:loc>
       <image:caption>' . $title . '</image:caption>
    </image:image>
	<lastmod>2017-11-10</lastmod>
	<changefreq>monthly</changefreq>
	<priority>0.8</priority>
    </url>');
}

fwrite($fp, '
</urlset>');

fclose($fp);

echo "Fim Sitemap";
