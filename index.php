<?php

require 'vendor/autoload.php';
$dirs = scandir('storage/');
// $file_name = mb_convert_encoding('storage/dǎo.dǎo123qwe', "UTF-8", "auto");
// echo $file_name;
// file_put_contents($file_name, 'd o');
// $num = 0;
foreach ($dirs as $key => $file) {
    if(is_file('storage/' . $file)){   
        // $num = $key + 191; 
        $num = (int)ltrim($file,'recite') - 2;
        if($num >=191)
        rename('storage/' . $file, 'storage/' . 'recite' . $num . '.mp3');
    }
}
// dd($dirs);
die();

use Overtrue\Pinyin\Pinyin;

class Syllable{

    public $content;
    public $dick;
    public $curl;
    public $pinyin;

    public $sb0 = '5';
    public $sb1 = '1';
    public $sb2 = '9';

    public function __construct($content = NULL)
    {
        $this->content = $content;
        $this->curl = new Curl();
        $this->pinyin = new Pinyin();
    }
    public function convert()
    {
        $this->normFilter();
        // 吃葡萄不吐葡萄皮9不吃葡萄倒吐葡萄皮9
        $this->getDick();
  // array[
  // 0 => "吃葡萄"
  // 1 => "不吃"
  // 2 => "葡萄皮"
  // ]
        dd($this->dick);
        $fragments = $this->getFragment();
// array[
//   0 => "吃葡萄不吐葡萄皮9"
//   1 => array[
//     0 => "不吃"
//   ]
//   2 => "葡萄倒吐"
//   3 => array[
//     0 => "葡萄皮"
//   ]
//   4 => "9你"
// ]
        $gap_content = $this->getGapContent($fragments);
        // 吃0葡0萄0不0吐0葡0萄0皮09不1吃0葡0萄0倒0吐0葡1萄1皮09
        // dd ($gap_content);
        $pinyin = $this->pinyin->convert($gap_content,PINYIN_UNICODE);
        return $pinyin;
    }

    private function getGapContent($fragments)
    {
        $result = '';
        foreach ($fragments as $key => $fragment) {
            if(is_string($fragment)){
                $result .= preg_replace('/([\x{4e00}-\x{9fa5}])/iu', '${1}' . $this->sb0, $fragment);
            }
            if(is_array($fragment)){
                $result .= preg_replace('/([\x{4e00}-\x{9fa5}])/iu', '${1}' . $this->sb1, $fragment[0]);
                $result = rtrim($result, $this->sb1) . $this->sb0;
            }
        }
        return $result;
    }

    private function normFilter()
    {
        $this->content = trim($this->content);
        $this->content = str_replace([1,2,3,4,5,6,7,8,9,0],['一','二','三','四','五','六','七','八','九','零'],$this->content);
        $this->content = str_replace(['“','”','：','！','？','，','、','。','.',',','?','!','…',"\n","\r","\t",' '],$this->sb2,$this->content);
        $this->content = preg_replace('/' . $this->sb2 . '+/',$this->sb2,$this->content);
    }

    private function getFragment()
    {
        $tmp_content = $this->content;
        $fragments = [];
        foreach ($this->dick as $key => $word) {
            if(strpos($tmp_content,$word) == 0)
                continue;
            $fragments[] = substr($tmp_content,0,strpos($tmp_content,$word));
            $fragments[] = [$word];
            $tmp_content = substr($tmp_content,strpos($tmp_content,$word)+strlen($word));
        }
        $fragments[] = $tmp_content;
        return $fragments;
    }

    private function getDick()
    {
        $url = "www.pullword.com/process.php";
        $param = array(
                "param1" => "0.3",
                "param2" => "0",
                "source" => $this->content,
            );
        $curl = new Curl();

        $res = $this->curl->post($url, $param);

        $dick = explode("\r\n",$res);

        $dick = array_slice($dick,16);
dd($dick);
        $dick = array_filter($dick);

        $this->dick = $this->filteRepeat($dick);
dd($this->dick);
    }

    /** 删除类似 葡萄 葡萄皮中的葡萄 */
    private function filteRepeat($dick)
    {
        foreach (range(1,3) as $j) {
            foreach ($dick as $key => $word) {
                if(isset($dick[$key-1]))
                    if(strpos($word,$dick[$key-1]))
                        unset($dick[$key-1]);

                if(isset($dick[$key+1]))
                    if(strpos($word,$dick[$key+1]))
                        unset($dick[$key+1]);
            }
            $dick = array_values($dick);
        }
        return $dick;
    }
}


$syllable = new Syllable("
        吃不吃葡萄
        ");
        // 你为什么不快乐？
        // 大部分人肯定会耸耸肩说：“没钱呗！”
        // 可是你有钱就会变得快乐吗？
        // 不见得，你现在肯定比上学时有钱，
        // 可是你却不如那时候快乐。
        // 那你究竟是为什么不快乐呢？
        // 大哲学家罗素列出了9大原因。

$syllables = $syllable->convert();
dd($syllables);

$sy_arr = include('sy_arr.php');
$sy_arr = array_flip($sy_arr);
// dd($sy_arr);
// $sy_arr = [
//   "chī" => 0,
//   "pú" => 23,
//   "táo" => 27,
//   "bù" => 7,
//   "tǔ" => 10,
//   "pí" => 14,
//   "dào" => 20,
// ];

foreach ($syllables as $key => &$syllable) {
    if(isset($sy_arr[$syllable]))
        $syllable = 'recite' . $sy_arr[$syllable];
}

dd($syllables);

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
  <title>Recite</title>

</head>
<body>

<?php
foreach($syllables as $syllable){
    if(!(int)$syllable)
        echo '<audio src="storage/' . $syllable . '.mp3" ';
    else
        echo "gap=" . $syllable . "></audio>\n";
}
?>
></audio>

  <script>
      var audios = document.getElementsByTagName("audio");

      function player(num){
          if(audios[num] == undefined)
              return;
          audios[num].play();
          audios[num].loop = false;
          console.log(audios[num]);
          audios[num].addEventListener('ended', function () {
              var gap = audios[num].getAttribute('gap');
              if(!gap)
                  gap = 0;
              console.log('num:' + num + '  |  ' + 'gap:' + gap);
              num++;

              setTimeout(function(){
                  player(num)
              },gap*10);
              return;
          }, false);

      }

      player(0);
  </script>
</body>
</html> 
