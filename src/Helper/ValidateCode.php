<?php
namespace Bybzmt\Blog\Common\Helper;

class ValidateCode
{
    private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';    //随机字符
    private $code;                           //验证码
    private $codelen = 4;                    //验证码长度
    private $width   = 118;                     //宽度
    private $height  = 36;                    //高度
    private $img;                            //图形资源句柄
    private $font;                           //指定的字体
    private $fontsize = 15;                  //指定字体大小
    private $fontcolor;                      //指定字体颜色 $white  =  imagecolorallocate ( $im ,  255 ,  255 ,  255 );

    public function __construct($width, $height)
    {
        $this->font =   ASSETS_PATH.'/fonts/elephant.ttf';
        $this->width  = $width ? $width : 118;
        $this->height = $height ? $height : 36;

        $this->createCode();
    }

    public function show($BgColor='',$FontColor='')
    {
        $this->createBg($BgColor);
        $this->createLine();
        $this->createFont($FontColor);
        $this->outPut();
    }

    public function getCode()
    {
        return $this->code;
    }

    private function createCode()
    {
        $_len = strlen($this->charset)-1;
        for ($i=0;$i<$this->codelen;$i++) {
            $this->code .= $this->charset[mt_rand(0,$_len)];
        }
    }

    private function createBg($BgColor='')
    {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        if( empty($BgColor) ){
            $color = imagecolorallocate($this->img, 109, 20, 162);
        }else{
            $color = imagecolorallocate($this->img, $BgColor[0], $BgColor[1], $BgColor[2]);
        }

        imagefilledrectangle($this->img,0,$this->height,$this->width,0,$color);
    }

    private function createFont($FontColor='')
    {
        $_x = $this->width / $this->codelen;
        for ($i=0;$i<$this->codelen;$i++) {
            if( empty($FontColor) ){
                $this->fontcolor = imagecolorallocate($this->img,255,255,255);
            }else{
                $this->fontcolor = imagecolorallocate($this->img,$FontColor[0],$FontColor[1],$FontColor[2]);
            }

            imagettftext($this->img,$this->fontsize,mt_rand(-30,30),$_x*$i+mt_rand(1,5),$this->height / 1.4,$this->fontcolor,$this->font,$this->code[$i]);
        }
    }

    private function createLine()
    {
        for ($i=0;$i<6;$i++) {
            $color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
        }
        for ($i=0;$i<100;$i++) {
            $color = imagecolorallocate($this->img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
            imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
        }
    }

    private function outPut()
    {
        header("Content-type:image/png");
        imagepng($this->img);
        imagedestroy($this->img);
    }

}
