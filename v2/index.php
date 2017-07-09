<?php
header('Content-Type:text/html;charset=utf-8');
require './MusicAPI.php';

$api = new MusicAPI();

$keyword = $_GET['songname'];
// $song_id = 30089608;
// $album_id = 18896;
// $mv_id = 5341392;

//返回搜索结果
//传入参数（搜索内容，返回的条数，偏移量, 类型）
$result = $api->search($keyword, 1);
$result = json_decode($result);
$song_id = $result->result->songs[0]->id;
// echo $song_id;
// print_r(json_decode($result));


//返回歌曲详情
$detail = $api->detail($song_id);
// print_r($detail);
$detail = json_decode($detail);
// var_dump($detail);
// var_dump($detail->songs[0]->name);
// var_dump($detail->songs[0]->ar[0]->name);

//返回歌曲链接
$mp3url = $api->mp3url($song_id);
// print_r($mp3url);
$mp3url = json_decode($mp3url);
$mp3url = $mp3url->data[0]->url;
// echo $mp3url;
//组合接口返回数据
$data = array(
    'songName' => $detail->songs[0]->name,
    'singer' => $detail->songs[0]->ar[0]->name,
    'mp3Url' => $mp3url,
  );
echo json_encode($data);

//歌曲专辑（不可用）
// $albums = $api->albums($album_id, 30);
// print_r($albums);

//歌曲歌词
// $lyric = $api->lyric($song_id);
// print_r($lyric);

//歌曲MV (如果有)
// $mv = $api->mv($mv_id);
// print_r($mv);




