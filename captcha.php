<?php
session_start();

// generate code
$captcha_code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ12345678"), 0, 5);
$_SESSION['captcha'] = $captcha_code;

// generate image
$width = 100;
$height = 50;
$image = imagecreatetruecolor($width, $height);

// color
$bg_color    = imagecolorallocate($image, 240, 240, 240);
$text_color  = imagecolorallocate($image, 200, 0, 0);
$line_color  = imagecolorallocate($image, 64, 64, 64);
$pixel_color = imagecolorallocate($image, 20, 100, 100);

// background
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// random line
for ($i = 0; $i < 5; $i++) {
    imageline($image, 0, rand()%$height, $width, rand()%$height, $line_color);
}

// random dots
for ($i = 0; $i < 200; $i++) {
    imagesetpixel($image, rand()%$width, rand()%$height, $pixel_color);
}

// font settings
$font = __DIR__ . '/assets/fonts/arial.ttf';
$font_size = 15; 
imagettftext($image, $font_size, rand(-10,10), 20, 35, $text_color, $font, $captcha_code);

// output
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
