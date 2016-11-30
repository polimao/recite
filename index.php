<?php

require 'vendor/autoload.php';

use Overtrue\Pinyin\Pinyin;

class Recite{

    public $content;
    public $curl;
    public $pinyin;

    public function __construct($content = NULL)
    {
        $this->content = $content;

        $this->curl = new Curl();
        $this->pinyin = new Pinyin();
    }
    public function convert()
    {
        // $dick = $this->getDick($this->content);
        // $fragment = $this->getFragment($dick);
        // var_dump($fragment);
        //
        //
        $this->normFilter();
        // echo $this->content;
dd($this->content);
        $this->toSyllable();
dd($this->content);
        $fragment = explode('',trim($this->content));
        dd($this->content,$fragment);
    }

    private function normFilter()
    {
        $this->content = trim($this->content);
        $this->content = str_replace([1,2,3,4,5,6,7,8,9,0],['一','二','三','四','五','六','七','八','九','零'],$this->content);
        $this->content = str_replace(['“','”','：','！','？','，','、','。','.',',','?','!','…',"\n","\t",' '],'9',$this->content);
        $this->content = preg_replace('/9+/','9',$this->content);
    }

    private function toSyllable()
    {

        // $syllable = $this->pinyin->convert($this->content,PINYIN_UNICODE);
        $syllables = [];
        for ($i=0; $i < mb_strlen($this->content); $i++) {
            $abc = mb_substr($this->content,$i,1);
            $key = $this->pinyin->convert($abc,PINYIN_UNICODE);
            $syllables[] = [$key[0],$abc];
        // dd(12);
        }


        dd($syllables);

        // foreach ($dick as $key => $word) {
        //     if($word >= 'A' && $word <='z')
        //         continue;
        //     $syllable = $pinyin->convert($word,PINYIN_UNICODE);
        //     var_dump($syllable);

        // }



        // ["dài","zhe","xī","wàng","qù","lǚ","xíng","bǐ","dào","dá","zhōng","diǎn","gèng","měi","hǎo"]

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

    private function getDick($content)
    {
        $url = "www.pullword.com/process.php";
        $param = array(
                "param1" => "0.9",
                "param2" => "0",
                "source" => $content,
            );
        $curl = new Curl();

        $res = $this->curl->post($url, $param);

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


$result = new Recite("
        吃葡萄不吐葡萄皮,不吃葡萄倒吐葡萄皮
        你为什么不快乐？
        大部分人肯定会耸耸肩说：“没钱呗！”
        可是你有钱就会变得快乐吗？
        不见得，你现在肯定比上学时有钱，
        可是你却不如那时候快乐。
        那你究竟是为什么不快乐呢？
        大哲学家罗素列出了9大原因。
        ");

var_dump($result->convert());






