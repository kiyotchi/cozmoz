<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------
// //Y.Paku 2011.09.08 イメージ環境ファイル
// ------------------------------------------------------------------------

// イメージ処理のライブラリ
//$config['image_library'] = 'C:/ImageMagick-6.7.2-Q16';
$config['image_library'] = '/usr/bin/convert';

// イメージアップロードの最大ファイルサイズ(KB)
$config['max_image_size'] = '5000';

// サムネールイメージの付加される文字列
$config['image_thumbnail_name'] = 'tn_';

/** コンテンツ画像 **/
// 携帯電話用イメージの保存ディレクトリ
$config['pitcherphone_image_dir'] = FCPATH . 'files/contents/pitcherphone/';
$config['pitcherphone_image_url'] = 'files/contents/pitcherphone/';

// スマートフォン用イメージの保存ディレクトリ
$config['smartphone_image_dir'] = FCPATH . 'files/contents/smartphone/';
$config['smartphone_image_url'] = 'files/contents/smartphone/';

// イメージ変換サイズ（幅のみ）
$config['pitcherphone_image_size'] = 220;
$config['smartphone_image_size'] = 320;

// サムネールイメージの変換サイズ（幅のみ）
$config['pitcherphone_thumbnail_size'] = 60;
$config['smartphone_thumbnail_size'] = 120;

/** 壁紙画像 **/
// 携帯電話用イメージの保存ディレクトリ
$config['pitcherphone_wallpaper_image_dir'] = FCPATH . 'files/wallpaper/pitcherphone/';
$config['pitcherphone_wallpaper_image_url'] = 'files/wallpaper/pitcherphone/';

// スマートフォン用イメージの保存ディレクトリ
$config['smartphone_wallpaper_image_dir'] = FCPATH . 'files/wallpaper/smartphone/';
$config['smartphone_wallpaper_image_url'] = 'files/wallpaper/smartphone/';

// イメージ変換サイズ（幅のみ）
$config['pitcherphone_wallpaper_image_size'] = 220;
$config['smartphone_wallpaper_image_size'] = 480;

// サムネールイメージの変換サイズ（幅のみ）
$config['pitcherphone_wallpaper_thumbnail_size'] = 110;
$config['smartphone_wallpaper_thumbnail_size'] = 120;

/** カレンダー画像 **/
// 携帯電話用イメージの保存ディレクトリ
$config['pitcherphone_calendar_image_dir'] = FCPATH . 'files/calendar/pitcherphone/';
$config['pitcherphone_calendar_image_url'] = 'files/calendar/pitcherphone/';

// スマートフォン用イメージの保存ディレクトリ
$config['smartphone_calendar_image_dir'] = FCPATH . 'files/calendar/smartphone/';
$config['smartphone_calendar_image_url'] = 'files/calendar/smartphone/';

// イメージ変換サイズ（幅のみ）
$config['pitcherphone_calendar_image_size'] = 220;
$config['smartphone_calendar_image_size'] = 480;

// サムネールイメージの変換サイズ（幅のみ）
$config['pitcherphone_calendar_thumbnail_size'] = 60;
$config['smartphone_calendar_thumbnail_size'] = 120;

?>
