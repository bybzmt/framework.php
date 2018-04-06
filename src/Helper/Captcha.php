<?php
namespace Bybzmt\Framework\Helper;

use Bybzmt\Framework\Helper;
use Bybzmt\Framework\Context;
use Gmagick;
use GmagickPixel;
use GmagickDraw;

/**
 * 生成验证码
 */
class Captcha extends Helper
{
    private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ1234567890';

	/**
	 * 验证码图片宽度 单位px
	 */
	protected $width = '130';

	/**
	 * 验证码图片高度 单位px
	 */
	protected $height= '40';

	/**
	 * 字体大小
	 */
	protected $fontsize = 25;

    protected $font = ASSETS_PATH.'/fonts/monaco.ttf';

	/**
	 * 背景色
	 */
	protected $backcolor = '#ffffff';

	/**
	 * 字体顔色
	 */
	protected $fontcolor = '#000000';

    protected $code;

    public function getCode()
    {
        if (!$this->code) {
            $_len = strlen($this->charset)-1;
            for ($i=0;$i<4;$i++) {
                $this->code .= $this->charset[mt_rand(0,$_len)];
            }
        }

        return $this->code;
    }

	public function show($fontColor, $backColor)
    {
        $this->fontcolor = $fontColor;
        $this->backcolor = $backColor;

        if (class_exists("Gmagick", false)) {
            $this->showGmagick();
        } else {
            $this->showGD();
        }
    }

	/**
	 * Gmagick 生成验证码
	 */
	public function showGmagick()
	{
        $randcode = $this->getCode();

		$image = new Gmagick();
		$pixel = new GmagickPixel();

		$pixel->setColor($this->backcolor);
		$image->newImage($this->width, $this->height, '#ffffff');

		//实例化画图类
		$draw = new GmagickDraw();

		$pixel->setColor($this->fontcolor);

		$num = mb_strlen($randcode);

        $this->fontsize = (int)($this->width / $num * 1.0);

		//设置字体
		$draw->setFontSize($this->fontsize);
		$draw->setFont($this->font);

		for ($i=0; $i<$num; $i++) {
			$str = mb_substr($randcode, $i, 1);

			//计算基准点
			$b_x =  ($this->fontsize/3) + ($this->width / $num * $i);
			$b_y = $this->height / 2 + $this->fontsize / 3;  //文字是以底边对齐的，根据文字大小计算出底边位置

			//随机偏移值
			$p_x = $b_x - 0;
			$p_y = $b_y + mt_rand(1, 10) - 5;
			$ro = mt_rand(1, 100) - 50;

			//计算旋转后$ro度后的圆上点的坐标
			$D = sqrt(pow($p_x,2)+pow($p_y,2));
			$S = atan2($p_x,$p_y)+deg2rad($ro);

			$p2_x = sin($S) * $D;
			$p2_y = cos($S) * $D;

			$draw->rotate($ro);
			$draw->annotate($p2_x, $p2_y, $str);
			$draw->rotate(-$ro);
		}

		$i = (int)($this->width * $this->height / 10);
		while ($i--) {
			$draw->point(mt_rand(0, $this->width), mt_rand(0, $this->height));
		}

		for($i=0; $i<20; $i++) {
			//绘制干扰线, 这边限制了生成图像最大宽为255,高为127
			$x  = mt_rand(0,$this->width);
			$y  = mt_rand(0,$this->height);
			$x2  = mt_rand(0,$this->width);
			$y2  = mt_rand(0,$this->height);

			$draw->setStrokeWidth(1);
			$draw->polyline(array(
				array('x'=> $x,'y'=>$y),
				array('x'=> $x2, 'y'=>$y2)
			));
		}

		$image->drawImage($draw);

		$image->setImageFormat('jpg');


        $this->_ctx->response->header("Content-type", "image/jpeg");
        $this->_ctx->response->end($image);
	}

    //GD库
	public function showGD()
    {
        $code = $this->getCode();
        $codelen = strlen($code);

        $img = imagecreatetruecolor($this->width, $this->height);

        $tmp = str_split(trim($this->backcolor, '#'), 2);
        $backcolor = imagecolorallocate($img, hexdec($tmp[0]), hexdec($tmp[1]), hexdec($tmp[2]));

        imagefilledrectangle($img,0,$this->height,$this->width,0,$backcolor);

        for ($i=0;$i<6;$i++) {
            $color = imagecolorallocate($img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            imageline($img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
        }
        for ($i=0;$i<100;$i++) {
            $color = imagecolorallocate($img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
            imagestring($img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
        }

        $tmp = str_split(trim($this->fontcolor, '#'), 2);
        $fontcolor = imagecolorallocate($img, hexdec($tmp[0]), hexdec($tmp[1]), hexdec($tmp[2]));

        $_x = $this->width / $codelen;
        for ($i=0;$i<$codelen;$i++) {
            imagettftext($img,$this->fontsize,mt_rand(-30,30),$_x*$i+mt_rand(1,5),$this->height / 1.4,$fontcolor,$this->font,$this->code[$i]);
        }

        $tmpfile = tempnam(sys_get_temp_dir(), 'captcha_');
        imagejpeg($img, $tmpfile);
        imagedestroy($img);

        $this->_ctx->response->header("Content-type", "image/jpeg");
        $this->_ctx->response->sendfile($tmpfile);

        unlink($tmpfile);
    }
}
