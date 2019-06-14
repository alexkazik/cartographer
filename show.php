<?php

if ($argc != 2) {
  die("Usage: show.php <areaN>\n");
}
$name = $argv[1];

$areas = 'Areas: ';
foreach (glob('*.txt') as $f) {
  $areas .= '<a href="'.substr($f, 0, -4).'.html">'.strtoupper(substr($f, 0, -4)).'</a> ';
}

$modalSize = 640;
$sizeMap = 160;
$sizeMapScaled = 320;
$sizeSource = 640;

$generatedFiles = @json_decode(@file_get_contents('cache/generated_files.json'), true);
if (!is_array($generatedFiles)) {
  $generatedFiles = [];
}

// parse file into map
$map = [];
$row = [];
foreach (explode("\n", file_get_contents($name.'.txt')) as $y => $ln) {
  if (preg_match('!^[-+]+$!', trim($ln))) {
    $map[] = $row;
    $row = [];
  } else {
    $ln = explode('|', $ln);
    foreach ($ln as $x => $e) {
      if (!array_key_exists($x, $row)) {
        $row[$x] = [];
      }
      $e = trim($e);
      if ($e != '') {
        $row[$x][] = $e;
      }
    }
  }
}

// remove empty borders
while (true) {
  foreach ($map[0] as $col) {
    if (count($col) != 0) {
      break 2;
    }
  }
  array_shift($map);
}
while (true) {
  $y = count($map) - 1;
  foreach ($map[$y] as $col) {
    if (count($col) != 0) {
      break 2;
    }
  }
  array_pop($map);
}
while (true) {
  foreach ($map as $row) {
    if (count($row[0]) != 0) {
      break 2;
    }
  }
  foreach ($map as &$row) {
    array_shift($row);
    unset($row);
  }
}
while (true) {
  $x = count($map[0]) - 1;
  foreach ($map as $row) {
    if (count($row[$x]) != 0) {
      break 2;
    }
  }
  foreach ($map as &$row) {
    array_pop($row);
    unset($row);
  }
}

ob_start();
echo '<html><head><style>
.card {
  background-position: -'.$sizeMap.'px 0px;
  background-size: '.(2 * $sizeMap).'px '.$sizeMap.'px;
}
'.file_get_contents('show.css').'</style>';
echo '<link rel="stylesheet" href="bootstrap.min.css">';
echo '</head><body>'.$areas.'<table border="0" cellspacing="0" cellpadding="0" style="background: url('.$name.'.jpeg); background-size: '.($sizeMap * count($map[0])).'px '.($sizeMap * count($map)).'px">';
$nextId = 1000;
$cards = [];
$cardActive = [];
$bigImg = imagecreatetruecolor($sizeMapScaled * count($map[0]), $sizeMapScaled * count($map));
imagefill($bigImg, 0, 0, imagecolorallocate($bigImg, 0, 0, 0));

foreach ($map as $y => $row) {
  echo '<tr>';
  foreach ($row as $x => $cols) {
    if (count($cols) == 0) {
      echo '<td><img src=map.jpeg width='.$sizeMap.'>';
    } elseif ($cols[0] == 'fog') {
      echo '<td><img src=fog.jpeg width='.$sizeMap.'>';
    } else {
      $subCards = [];
      $show = null;
      foreach ($cols as $e) {
        $gold = $e[0] == '!';
        if ($gold) {
          $e = substr($e, 1);
        }
        list($cnu, $cid) = explode(':', $e);
        $subCards[] = [
          $gold,
          $cnu,
          $cid,
        ];
        if ($show === null) {
          $show = $cid;
        }
      }
      $thisId = $nextId++;
      usort($subCards, function ($a, $b) {
        return strcmp($a[1].$a[2], $b[1].$b[2]);
      });
      echo '<td><a href="javascript:card(\''.$thisId.'\')"><img id='.$thisId.' src='.(count($subCards) == 1 ? 'empty' : 'multiple').'.png width='.$sizeMap.'></a>';
      $cards[$thisId] = $subCards;
      $cardActive[$thisId] = $show;
      $img = imagecreatefromjpeg('cache/'.$show.'f.jpeg');
      imagecopyresampled($bigImg, $img, $x * $sizeMapScaled, $y * $sizeMapScaled, 0, 0, $sizeMapScaled, $sizeMapScaled, $sizeSource, $sizeSource);
      imagedestroy($img);
    }
  }
}
echo '</table>'.PHP_EOL;
imagejpeg($bigImg, 'web/'.$name.'.jpeg');
imagedestroy($bigImg);
echo file_get_contents('show.html');
echo '<script>';
echo file_get_contents('show.js');
echo 'var cardActive = '.json_encode($cardActive).';';
echo 'var cards = '.json_encode($cards).';';
echo '</script>';
file_put_contents('web/'.$name.'.html', ob_get_contents());
ob_clean();

$new = array_map(
  function ($line) {
    return array_merge([[]], $line, [[]]);
  }, $map
);
$lineLength = count($new[0]);
$newLine = array_fill(0, $lineLength, []);
$lineLength += 2;
$new = array_merge([$newLine], $new, [$newLine]);
$out = '';
foreach ($new as $row) {
  do {
    $empty = true;
    foreach ($row as &$col) {
      if (count($col) > 0) {
        $e = array_shift($col);
        if (strlen($e) < 10) {
          $e = ' '.$e;
        }
        $out .= ' '.str_pad($e, 10, ' ', STR_PAD_RIGHT).' |';
        $empty = false;
      } else {
        $out .= '            |';
      }
      unset($col);
    }
    $out .= "\n";
  } while (!$empty);
  foreach ($row as $col) {
    $out .= '------------+';
  }
  $out .= "\n";
}

file_put_contents($name.'.txt', $out);
