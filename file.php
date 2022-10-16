<?php

if(array_key_exists('file', $_REQUEST)) {
  $file = $_REQUEST['file'];
} else {
  $file = $_REQUEST['id'];
}

$files = array(
  'PS3FP9NC.swf' => array(
    'path' => __DIR__ . '/files/PS3FP9NC.swf',
    'type' => 'application/x-shockwave-flash',
    'kind' => 'flash',
    'filename' => 'PS3FP9NC.swf',
  ),
  'PS3Loader.swf' => array(
    'path' => __DIR__ . '/files/PS3Loader.swf',
    'type' => 'application/x-shockwave-flash',
    'kind' => 'flash',
    'filename' => 'PS3Loader.swf',
  ),
  'RFpEOTl4eUNZTGNWL2xtS2lMbjIvdz09' => array(
    'path' => __DIR__ . '/files/FP9Test.swf',
    'type' => 'application/x-shockwave-flash',
    'kind' => 'flash',
    'filename' => 'FP9Test.swf',
  ),
  'dENwbEo0TEY0QlVxclEvdk9FWGtWUzByS05FZllRZnFHM0JyWWhSUlF5UT0=' => array(
    'path' => __DIR__ . '/files/xframework.min.js',
    'type' => 'application/x-javascript',
    'kind' => 'js',
    'filename' => 'xframework.min.js',
  ),
  'V3ZGdS8zOFk5dU5oeldSSkRZVEMxc3hLZW45SGdqc3lPL29XRWNSdnJQaz0=' => array(
    'path' => __DIR__ . '/files/biginteger.min.js',
    'type' => 'application/x-javascript',
    'kind' => 'js',
    'filename' => 'biginteger.min.js'
  ),
  'nofsm_patch_480.bin' => array(
    'path' => __DIR__ . '/files/nofsm_patch_480.bin',
    'type' => 'application/octet-stream',
    'kind' => 'bin',
    'filename' => 'nofsm_patch_480.bin',
  ),
  'nofsm_patch_481.bin' => array(
    'path' => __DIR__ . '/files/nofsm_patch_481.bin',
    'type' => 'application/octet-stream',
    'kind' => 'bin',
    'filename' => 'nofsm_patch_481.bin',
  ),
  'nofsm_patch_482.bin' => array(
    'path' => __DIR__ . '/files/nofsm_patch_482.bin',
    'type' => 'application/octet-stream',
    'kind' => 'bin',
    'filename' => 'nofsm_patch_482.bin',
  ),
  'nofsm_patch_483.bin' => array(
    'path' => __DIR__ . '/files/nofsm_patch_483.bin',
    'type' => 'application/octet-stream',
    'kind' => 'bin',
    'filename' => 'nofsm_patch_483.bin',
  ),
  'nofsm_patch_484.bin' => array(
    'path' => __DIR__ . '/files/nofsm_patch_484.bin',
    'type' => 'application/octet-stream',
    'kind' => 'bin',
    'filename' => 'nofsm_patch_484.bin',
  ),
  'nofsm_patch_485.bin' => array(
    'path' => __DIR__ . '/files/nofsm_patch_485.bin',
    'type' => 'application/octet-stream',
    'kind' => 'bin',
    'filename' => 'nofsm_patch_485.bin',
  ),
  'nofsm_patch_486.bin' => array(
    'path' => __DIR__ . '/files/nofsm_patch_486.bin',
    'type' => 'application/octet-stream',
    'kind' => 'bin',
    'filename' => 'nofsm_patch_486.bin',
  ),
  'nofsm_patch_487.bin' => array(
    'path' => __DIR__ . '/files/nofsm_patch_487.bin',
    'type' => 'application/octet-stream',
    'kind' => 'bin',
    'filename' => 'nofsm_patch_487.bin',
  ),
  'nofsm_patch_488.bin' => array(
    'path' => __DIR__ . '/files/nofsm_patch_488.bin',
    'type' => 'application/octet-stream',
    'kind' => 'bin',
    'filename' => 'nofsm_patch_488.bin',
  ),
  'nofsm_patch_489.bin' => array(
    'path' => __DIR__ . '/files/nofsm_patch_489.bin',
    'type' => 'application/octet-stream',
    'kind' => 'bin',
    'filename' => 'nofsm_patch_489.bin',
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
