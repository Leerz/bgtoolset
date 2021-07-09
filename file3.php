<?php

$file = $_REQUEST['file'];

$files = array(
  'FP9TesterK3.swf' => array(
    'path' => __DIR__ . '/files/FP9TesterK3.swf',
    'type' => 'application/x-shockwave-flash',
    'kind' => 'flash',
  ),
  'PS3FP9NC3.swf' => array(
    'path' => __DIR__ . '/files/PS3FP9NC3.swf',
    'type' => 'application/x-shockwave-flash',
    'kind' => 'flash',
  ),
  'PS3LoaderK3.swf' => array(
    'path' => __DIR__ . '/files/PS3LoaderK3.swf',
    'type' => 'application/x-shockwave-flash',
    'kind' => 'flash',
  ),
  'biginteger.pmin.js' => array(
    'path' => __DIR__ . '/files/biginteger.pmin.js',
    'type' => 'application/x-javascript',
    'kind' => 'js',
  ),
  'framework.pmin.js' => array(
    'path' => __DIR__ . '/files/framework.pmin.js',
    'type' => 'application/x-javascript',
    'kind' => 'js',
  ),
  'nofsm_patch_488.bin' => array(
    'path' => __DIR__ . '/files/nofsm_patch_488.bin',
    'type' => 'application/octet-stream',
    'kind' => 'bin',
  )
);

$item = $files[$file];

if(!file_exists($item['path'])) {
  http_response_code(404);
  exit;
}

header('Content-Type: ' . $item['type']);
header('Content-Length: ' . filesize($item['path']));
if($item['kind'] == 'flash') {
  header('Content-Transfer-Encoding: binary');
}
if($item['kind'] == 'js') {
  header('Content-Description: File Transfer');
  header('Content-Disposition: inline; filename="' . $file . '"');
}
if($item['kind'] == 'bin') {
  header('Content-Transfer-Encoding: binary');
  header('Content-Disposition: attachment; filename="' . $file . '"');
}
readfile($item['path']);
