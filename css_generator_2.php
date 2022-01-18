<?php 
function my_scan($arg)
{
    $opendir = opendir($arg);
    while (false !== ($read = readdir($opendir)))
    {
        $chemin = $arg . "/" . $read;
        if ($read == "." || $read == "..") continue;
        else    $PATH[$chemin] = $read;
    }
    if (isset($PATH)) return $PATH;
}

function my_scan_recursive($arg)
{
    $PATH = my_scan($arg);
    static $PNG;
    if (isset($PATH)) foreach ($PATH as $chemin => $value)
    {
        if (is_file($chemin) && mime_content_type($chemin) == "image/png" || mime_content_type($chemin) == "image/jpg") $PNG[] = $chemin;
        if (is_dir($chemin)) my_scan_recursive($chemin);
    }
    return $PNG;
}

function my_merge($picture,$L,$H, $spritename, $column, $padding, $size,$nameStyle,$namePNG)
{
    $maxlarge = max($L);  $maxhaut = max($H); $nbImage = count($L);
    if ($column == null) $column = round(sqrt($nbImage));
    if ($size <> 0)   
    {
        $maxlarge = $size; $maxhaut = $size;
    }
    $cadre = imagecreatetruecolor($maxlarge*$column, $maxhaut* (ceil($nbImage/$column)));
    $colorcadre = imagecolorallocate($cadre,0,0,0);
    imagecolortransparent($cadre,$colorcadre); 
    $x=0; $y=0; $i=0;
    foreach ($picture as $img)
    {
        if($size == 0 || $size == '0')  {
            $xL = imagesx($img) ; $yH = imagesy($img);
        }
        else{
            $xL = $size; $yH = $size;  
        } 
        imagecopyresampled($cadre,$img,$x+round(($maxlarge-$xL)/2),$y+round(($maxhaut-$yH)/2),0,0,$xL,$yH,$L[$i]+$padding,$H[$i]+$padding); 
        if($x<$maxlarge*($column-1)) $x+=$maxlarge;
        else{
            $y+=$maxhaut;
            $x=0;}
        $i++;
    }
    imagepng($cadre,$spritename . ".png");
    generate_css($nameStyle,$L, $H, $spritename, $column, $padding, $size, $maxlarge, $maxhaut,$namePNG);
}

function generate_css($nameStyle,$L, $H, $spritename, $column, $padding, $size, $maxlarge, $maxhaut,$namePNG)
{
    $file = fopen ($nameStyle . '.css','w+');
    fwrite($file,".sprite-{$spritename}\n{\nbackground-image: url({$spritename}.png);\nbackground-repeat: no-repeat;\ndisplay: block;\n}\n");
    $x=0; $y=0; $i=0;
    foreach ($namePNG as $name)
    {
        $width = $L[$i] + $padding;
        $height = $H[$i] + $padding;
        if ($size == 0)
        {
            $x += round(($maxlarge-$L[$i])/2); $y += round(($maxhaut-$H[$i])/2);
        }
        else
        {
            $width = $size;
            $height = $size;
        }
        fwrite($file,".$name\n{\nwidth: " . $width  . " px;\nheight: " . $height . " px;\nbackground-position: " . $x . "px " . $y . "px;\n}\n");
        if($x<$maxlarge*($column-1)) $x+=$maxlarge;
        else{
            $y+=$maxhaut;
            $x=0;
        }
        $i++;
    }
}

function set_option($argv)
{
    $spriteName = 'sprite'; 
    $column =null; $padding = 0; $size = 0; $nameStyle = 'style';
    foreach ($argv as $arg)
    {
        if (is_dir($arg) && in_array('-r',$argv) || in_array('--recursive',$argv)) $PNG = my_scan_recursive($arg);
        if (is_dir($arg) && !in_array('-r',$argv) && !in_array('--recursive',$argv))
        {
            $PATH = my_scan($arg);
            if (isset($PATH)) foreach ($PATH as $chemin => $v) if (is_file($chemin) && mime_content_type($chemin) == "image/png") $PNG[] = $chemin;
        }
        if ( is_file($arg) AND mime_content_type($arg) == 'image/png') $PNG_file[] = $arg;
        if ($arg == '-i' || $arg == '--output-image') $spriteName = $argv[array_search($arg,$argv)+1];
        if ($arg == '-s' || $arg == '--output-style') $nameStyle = $argv[array_search($arg,$argv)+1];
        if ($arg == '-c' || $arg == '--columns-number') $column = $argv[array_search($arg,$argv)+1];
        if ($arg == '-p' || $arg == '--padding') $padding = $argv [array_search($arg,$argv)+1];
        if ($arg == '-o' || $arg == '--override-size') $size = $argv[array_search($arg,$argv)+1];
    }
    if (isset($PNG) || isset($PNG_file)) 
    {
        if(isset ($PNG) AND $PNG_file) $TabPNG = array_merge($PNG,$PNG_file);
        if(isset ($PNG) AND !isset($PNG_file)) $TabPNG = $PNG;
        if(isset($PNG_file) AND !isset($PNG)) $TabPNG = $PNG_file; 
        foreach ( $TabPNG as $img)
        {
            $taille = getimagesize($img);
            $largeur[] = $taille[0];    $hauteur[] = $taille[1];    $picture[] = imagecreatefrompng($img); 
            $namePNG [] = basename($img);
        }
        my_merge($picture,$largeur,$hauteur,$spriteName,$column,$padding,$size,$nameStyle,$namePNG);
        echo "\nYour sprite is ready, name sprite : $spriteName.png\n";
        echo "Your CSS is ready, name CSS file : $nameStyle.css\n\n";
    }
    else
        echo "\n\n___NO PNG FOUND___\n\n\n";
}
set_option($argv);
