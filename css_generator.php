<?php
abstract class A_MergeSpriteCss {
    protected $Pictures;
    protected $ListNomsPng;
    protected $TabLargeur;
    protected $TabHauteur;
    protected $largMax;
    protected $hautMax;
    protected $nbImages;
    protected $TabPNG;
    protected $nomSprite = 'sprite';
    protected $nomCSS = 'style';
    protected $column;
    protected $padding;

    function data_png() {
        foreach ($this->TabPNG as $png) {
            $taille = getimagesize($png);
            $largeur[] = $taille[0];    $hauteur[] = $taille[1];
            $picture[] = imagecreatefrompng($png);
        }
        
        foreach ($this->TabPNG as $png) {
            $name = basename($png);
            $list_noms[] = $name;
        }

        $this->Pictures = $picture;         $this->ListNomsPng = $list_noms;    $this->nbImages = count($largeur);
        $this->TabLargeur = $largeur;       $this->TabHauteur = $hauteur;
        $this->largMax =  max($largeur);    $this->hautMax = max($hauteur);
    }

    function set_column_number($nb =null) {
        if (ctype_digit($nb) AND $nb<$this->nbImages && $nb != null)  $this->column = $nb;
        else
            $this->column = round(sqrt($this->nbImages));
    }
    function set_nom_Sprite($nom_Sprite='sprite') { 
        $this->nomSprite = $nom_Sprite;
    }
    function set_nom_CSS($nom_css='style') {
        $this->nomCSS = $nom_css;
    }
    function set_padding($_padding) {
        if( ctype_digit($_padding)) {
            $this->largMax += $_padding;     
            $this->hautMax += $_padding;
        }
    }
    function set_oversize(int $newWidth = null) {
        if ($newWidth != null AND ctype_digit($newWidth)) { 
            $h=0;
            foreach ($this->TabHauteur as $key=>$value) {
                if ($newWidth<$value) {
                    $this->TabHauteur[$key] = round($value/($this->TabLargeur[$h]/$newWidth));
                }
                $h++;
            }
            $this->hautMax =  max($this->TabHauteur);
            foreach ($this->TabLargeur as $key=>$value) if($newWidth<$value) $this->TabLargeur[$key] = $newWidth;
            $this->largMax =  max($this->TabLargeur);
        }
        else
            null;
    }
    function set_arg_png($arg) {
        foreach ($arg as $value) $this->TabPNG[] = $value;
    }
    function get_sprite() {
        return $this->nomSprite;
    }
    function get_css() {
        return $this->nomCSS;
    }
    function get_TabPNG() {
        if (isset($this->TabPNG)) return true;
    }
}

class FusionImage extends A_MergeSpriteCss {
    function scan_only_folder($folder) {
        $odir = opendir($folder);
        while (($file = readdir($odir)) !== false) {
            $path = $folder . "/" . $file;
            if ($file == "." || $file == "..")  continue;
            else $TabPath[$path] = $file;
        }
        closedir($odir);
        if (isset($TabPath)) {
            foreach ($TabPath as $path=>$value) if(is_file($path) && mime_content_type($path) == "image/png" ||mime_content_type($path) == 'image/jpg')  $this->TabPNG[] = $path;
            return $TabPath;
        }
    }

    function my_scandir($folder) {
        $TabPath = $this->scan_only_folder($folder);
        if (isset ($TabPath)) foreach ($TabPath as $chemin => $nom) if (is_dir($chemin)) $this->my_scandir($chemin);
    }

    function my_merge_image() {
        $cadreblanche = imagecreatetruecolor($this->largMax*$this->column,$this->hautMax*(ceil($this->nbImages/$this->column)));
        $colorcadre = imagecolorallocate($cadreblanche,0,0,0);
        imagecolortransparent($cadreblanche, $colorcadre); 
        $x=0; $y=0; $i=0;
        foreach ($this->Pictures as $img) {
            $widht = imagesx($img);
            $height = imagesy($img);
            imagecopyresampled($cadreblanche,$img,$x+round((($this->largMax-$this->TabLargeur[$i])/2)),$y+round((($this->hautMax-$this->TabHauteur[$i]))/2),0,0,$this->TabLargeur[$i],$this->TabHauteur[$i],$widht,$height);
            if($x<$this->largMax*($this->column-1))  $x+=$this->largMax;
            else {
                $y+= $this->hautMax;
                $x=0;
            }
            $i++;
        }
        imagepng($cadreblanche,$this->nomSprite . ".png");
    }
    
    function my_generate_css() {
        $css_file = fopen("{$this->nomCSS}.css",'w+');
        fwrite($css_file,".sprite-{$this->nomSprite}\n{\nbackground-image: url({$this->nomSprite}.png);\nbackground-repeat: no-repeat;\ndisplay: block;\n}\n");
        $i=0; $x=0; $y=0;
        foreach ($this->ListNomsPng as $nom) {
            $width = $this->TabLargeur[$i];
            $height = $this->TabHauteur[$i];
            $xpos = $x+round((($this->largMax-$width)/2));
            $ypos = $y+round((($this->hautMax-$height)/2));
            fwrite($css_file,".$nom\n{\nwidth: " . $width   . " px;\nheight: " . $height . " px;\nbackground-position: $xpos px  $ypos px;\n}\n");
            if ($x<$this->largMax*($this->column-1))  $x+=$this->largMax;
            else {
                $y+= $this->hautMax;  
                $x=0;
            }
            $i++;
        }
    }
}

function class_starter(...$ARGUMENTS) {
    $fusion = new FusionImage();
    $i = 'sprite'; $s = 'style'; $c = 1; $o = null; $p = 0;
    foreach ($ARGUMENTS as $arg) {
        if (is_dir($arg) AND in_array("-r",$ARGUMENTS) || in_array("--recursive",$ARGUMENTS) ) $fusion->my_scandir($arg);
        if (is_dir($arg) AND !in_array("-r",$ARGUMENTS) AND !in_array("--recursive",$ARGUMENTS) ) $fusion->scan_only_folder($arg);
        if (is_file($arg) && mime_content_type($arg) == 'image/png' || mime_content_type($arg) == 'image/jpg') $Tab_png_arg [] = $arg; 
        if ($arg == "-i" || $arg == '--output-image')  $i =  $ARGUMENTS[array_search($arg,$ARGUMENTS)+1];
        if ($arg == "-s" || $arg == "--output-style") $s = $ARGUMENTS[array_search($arg,$ARGUMENTS)+1];
        if ($arg == "-c" || $arg == "--columns-number") $c = $ARGUMENTS[array_search($arg,$ARGUMENTS)+1];
        if ($arg == "-p" || $arg == "--padding" ) $p = $ARGUMENTS[array_search($arg,$ARGUMENTS)+1];
        if ($arg == "-o" || $arg == "--override-size") $o = $ARGUMENTS[array_search($arg,$ARGUMENTS)+1];
    }
    if (isset($Tab_png_arg)) $fusion->set_arg_png($Tab_png_arg);
    $check = $fusion->get_TabPNG();
    if (!isset($check )) {
        echo "\n\n\n_____NO PNG FOUND !_____\n\n\n";
        exit;
    }
    $fusion->data_png();
    $fusion->set_nom_Sprite($i);
    $fusion->set_nom_CSS($s);
    $fusion->set_column_number($c);
    $fusion->set_oversize($o);
    $fusion->set_padding($p);
    $fusion->my_merge_image();
    $fusion->my_generate_css();
    echo "\nYour sprite name =======>       " . $fusion->get_sprite() . ".png" . "\n";
    echo "Your CSS name    =======>       " . $fusion->get_css() . ".css" . "\n\n";
}
class_starter(...$argv);