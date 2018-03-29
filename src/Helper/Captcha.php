<?php
namespace Bybzmt\Blog\Common\Helper;

use Bybzmt\Blog\Common\Helper;
use Gmagick;
use GmagickPixel;
use GmagickDraw;

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

	/**
	 * 背景色
	 */
	protected $backcolor = 'white';

	/**
	 * 字体顔色
	 */
	protected $fontcolor = 'black';

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

	/**
	 * Gmagick 生成验证码
	 */
	public function show()
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
		$draw->setFont(ASSETS_PATH.'/fonts/monaco.ttf');

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

        return $image;
	}

}
