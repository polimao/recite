<?php

require 'vendor/autoload.php';

function dd()
{
    $param = func_get_args();
    foreach ($param as $key => &$value) {
        is_array($value) and $value = json_encode($value,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    $print = join($param,"\t");
    echo $print;
    die();
}

function post_curl($url,$post_data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $result = curl_exec($ch);
    if(!$result){
        dd('error','cUrl', $url ,json_encode($post_data));
        return false;
    }
    curl_close($ch);

    return $result;
}


class Recite{

    public $content;

    public function __construct($content)
    {
        $this->content = "
        吃葡萄不吐葡萄皮,不吃葡萄倒吐葡萄皮
        你为什么不快乐？

        大部分人肯定会耸耸肩说：“没钱呗！”

        可是你有钱就会变得快乐吗？

        不见得，你现在肯定比上学时有钱，

        可是你却不如那时候快乐。

        那你究竟是为什么不快乐呢？

        大哲学家罗素列出了9大原因。
        ";
        }
    public function convert()
    {
        $dick = $this->getDick($this->content);
        $fragment = $this->getFragment($dick);
        var_dump($fragment);
    }

    private function getFragment($dick)
    {
        $result = [];
        foreach ($dick as $key => $word) {
            $result[] = substr($content,0,strpos($content,$word));
            $result[] = [$word];
            $content = substr($content,strpos($content,$word)+strlen($word));
        }
        $result[] = $content;
        return $result;
    }

    public function getDick($content)
    {           
        $url = "http://www.pullword.com/process.php";
        $param = array(
                "param1" => "0.9",
                "param2" => "0",
                "source" => $content,
            );

        $res = post_curl($url, $param);

        $dick = explode("\r\n",$res);

        $dick = array_slice($dick,6);

        $dick = array_filter($dick);

        return $this->filteRepeat($dick);
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


// $content = $_GET['content']?:'李彦宏是马云最大威胁嘛？';

$result = new Recite($content);

var_dump($result->convert());


// use Overtrue\Pinyin\Pinyin;

// $pinyin = new Pinyin();

// foreach ($dick as $key => $word) {
//     if($word >= 'A' && $word <='z')
//         continue;
//     $syllable = $pinyin->convert($word,PINYIN_UNICODE);
//     var_dump($syllable);

// }



// ["dài","zhe","xī","wàng","qù","lǚ","xíng","bǐ","dào","dá","zhōng","diǎn","gèng","měi","hǎo"]






