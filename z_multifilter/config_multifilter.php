<?php
/*
Namen der Attribute mit Slider
*/
$this->aSliderAttributes = array("Alter","Stockmaß");
/*
Einheiten der Attribute mit Slider
*/
$this->aSliderUnits = array("Alter" => "J.", "Stockmaß" => "cm" );
/*
Namen der Attribute mit Farb-Icons
*/
$this->aColorSwatchCategories = array("Farbe","Color");
/*
Namen der Attribute mit Dropdowns
*/
$this->aDropdownCategories = array();
/*
Namen der Attribute mit UND-Verknüpfung
*/
$this->aUseAndAttributes = array("");
/*
Farben der Farbicons nach Sprach-ID
*/
//Deutsch
$this->aColorSwatchColors[0] = array(
    "weiß"=>"#ffffff",
    "schwarz"=>"#000000",
    "grau"=>"#999999",
    "orange"=>"#FF8C00",
    "gelb"=>"#FFFF00",
    "rot"=>"#FF0000",
    "blau"=>"#0000FF",
    "grün"=>"#008000",
    "dark blue"=>"#00008B",
    "smoke gray"=>"#DCDCDC",
    "super blue"=>"#191970",
    "clover"=>"#509D69",
    "violet"=>"#58499A",
    "nocturne"=>"#462B3E",
    "white"=>"#ffffff",
);
//Englisch
$this->aColorSwatchColors[1] = array(
    "white"=>"#ffffff",
    "black"=>"#000000",
    "gray"=>"#999999",
    "orange"=>"#FF8C00",
    "yellow"=>"#FFFF00",
    "red"=>"#FF0000",
    "blue"=>"#0000FF",
    "green"=>"#008000",
    "dark blue"=>"#00008B",
    "smoke gray"=>"#DCDCDC",
    "super blue"=>"#191970",
    "clover"=>"#509D69",
    "violet"=>"#58499A",
    "nocturne"=>"#462B3E",
    "white"=>"#ffffff",
);
/*
Bilder der Farbicons nach Sprach-ID
*/
//Deutsch
$this->aColorSwatchImages[0] = array(
    "gelb/schwarz"=>"yellow-black.png",
    "braun/grün"=>"brown-green.png",
);
//Englisch
$this->aColorSwatchImages[1] = array(
    "yellow/black"=>"yellow-black.png",
    "brown/green"=>"brown-green.png",
);

/*
Sortierungen nach SprachId
*/
$this->aLangSortArrays[0] = array(
    "EU-Größe" => array('XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'),
);
$this->aLangSortArrays[1] = array(
    "EU-Size" => array('XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'),
);

/*
Attribute Mappings nach SprachId
*/
$this->aLangColorMappings[0] = array(
    'Dark Blue'  => 'Blau',
    'Super Blue' => 'Blau',
    'Clover' => 'Grün',
);
$this->aLangColorMappings[1] = array(
    'Dark Blue'  => 'Blue',
    'Super Blue' => 'Blue',
);

/*
Attribute Mappings nach SprachId und Kategorie
*/
$this->aLangColorCategoryMappings[0]['d863b76c6bb90a970a5577adf890e8cd'] = array(
    'Dark Blue'  => 'Blau Sie',
    'Super Blue' => 'Blau Sie',
);
$this->aLangColorCategoryMappings[0]['d8665fef35f4d528e92c3d664f4a00c0'] = array(
    'Blau'  => 'Blau Er',
    'Super Blue' => 'Blau Er',
);

/*
Attribute in der Suche nach SprachId (erfordert Modul "Searchpack XL")
*/
$this->aLangSearchAttributes[0] = array(
    'Hersteller', 'Preis', 'Größe', 'Farbe', 'Einsatzbereich', 'Lieferumfang',
);
$this->aLangSearchAttributes[1] = array(
    'Manufacturer', 'Price', 'Size', 'Color',
);
