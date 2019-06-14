<?php

$size = 640;

if (!file_exists('cache')) {
  mkdir('cache');
}
if (!file_exists('web')) {
  mkdir('web');
}
if (!file_exists('web/card')) {
  mkdir('web/card');
}

$cachedFiles = @json_decode(@file_get_contents('cache/files.json'), true);
if (!is_array($cachedFiles)) {
  $cachedFiles = [];
}

$files = [];
foreach (glob('images-c*d*/*.jpeg') as $cropFile) {
  $fileName = basename($cropFile);
  $cacheFile = 'cache/'.$fileName;
  $cropMd5 = md5_file($cropFile);
  $name = substr($fileName, 0, -5);
  if (!file_exists($cacheFile) || !array_key_exists($name, $cachedFiles) || $cachedFiles[$name] != $cropMd5) {
    $big = imagecreatefromjpeg($cropFile);
    $scaled = imagescale($big, $size, $size, IMG_BICUBIC);
    imagedestroy($big);
    imagejpeg($scaled, $cacheFile);
    imagedestroy($scaled);
    $cachedFiles[$name] = $cropMd5;
    echo $name."\n";
  }
  $files[$name] = null;
}
file_put_contents('cache/files.json', json_encode($cachedFiles, JSON_PRETTY_PRINT));

$generatedFiles = @json_decode(@file_get_contents('cache/generated_files.json'), true);
if (!is_array($generatedFiles)) {
  $generatedFiles = [];
}

$filesToDelete = array_flip(glob('web/card/*.jpeg'));

foreach ($files as $name => $null) {
  if (substr($name, -1) == 'b' && array_key_exists(substr($name, 0, -1).'f', $files)) {
    $name = substr($name, 0, -1);
    $outName = 'web/card/'.$name.'.jpeg';
    $dstmd5 = $cachedFiles[$name.'b'].$cachedFiles[$name.'f'];
    if (!array_key_exists($name, $generatedFiles) || $generatedFiles[$name] != $dstmd5) {
      $img = imagecreatetruecolor(2 * $size, $size);

      $inp = imagecreatefromjpeg('cache/'.$name.'b.jpeg');
      imagecopy($img, $inp, 0, 0, 0, 0, $size, $size);
      imagedestroy($inp);

      $inp = imagecreatefromjpeg('cache/'.$name.'f.jpeg');
      imagecopy($img, $inp, $size, 0, 0, 0, $size, $size);
      imagedestroy($inp);

      imagejpeg($img, $outName, 75);
      imagedestroy($img);

      $generatedFiles[$name] = $dstmd5;
    }
    unset($filesToDelete[$outName]);
  }
}
file_put_contents('cache/generated_files.json', json_encode($generatedFiles, JSON_PRETTY_PRINT));

foreach ($filesToDelete as $file => $dummy) {
  unlink($file);
}
