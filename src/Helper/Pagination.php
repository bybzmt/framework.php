<?php
namespace Bybzmt\Framework\Helper;

class Pagination
{
    //固定数量翻页
    public function style1(int $count, int $size, int $current, callable $fn)
    {
        $pageNum = 10;

        if ($current < 1) {
            $current = 1;
        }

        $i = $current - (int)($pageNum / 2);
        if ($i < 1) {
            $i = 1;
        }

        $max = ceil($count/$size);
        if ($max < 1) {
            $max = 1;
        }

        $previous = $current - 1;
        $next = $current + 1;

        $end = $i+($pageNum-1);

        $pages = [];
        if ($i != 1) {
            $pages[] = ['page'=>1, 'url'=>$fn(1), 'active'=>1==$current, 'disabled'=>false];
        }
        for (; $i <= $end; $i++) {
            $pages[] = ['page'=>$i, 'url'=>$fn($i), 'active'=>$i==$current, 'disabled'=>$i>$max];
        }
        if ($end < $max) {
            $pages[] = ['page'=>$max, 'url'=>$fn($max), 'active'=>$max==$current, 'disabled'=>false];
        }

        return array(
            'previous' => ['page'=>$previous, 'url'=>$fn($previous), 'active'=>false, 'disabled'=>$previous < 1],
            'next' => ['page'=>$next, 'url'=>$fn($next), 'active'=>false, 'disabled'=>$next > $max],
            'pages' => $pages,
        );
	}

    //按数据量显示页数
    public function style2(int $count, int $size, int $current, callable $fn)
    {
        $pageNum = 10;

        if ($current < 1) {
            $current = 1;
        }

        $i = $current - (int)($pageNum / 2);
        if ($i < 1) {
            $i = 1;
        }

        $max = ceil($count/$size);
        if ($max < 1) {
            $max = 1;
        }

        $previous = $current - 1;
        $next = $current + 1;

        $end = $i+($pageNum-1);

        $pages = [];
        if ($i != 1) {
            $pages[] = ['page'=>1, 'url'=>$fn(1), 'active'=>1==$current, 'disabled'=>false];
        }
        for (; $i <= $end && $i<=$max; $i++) {
            $pages[] = ['page'=>$i, 'url'=>$fn($i), 'active'=>$i==$current, 'disabled'=>$i>$max];
        }
        if ($end < $max) {
            $pages[] = ['page'=>$max, 'url'=>$fn($max), 'active'=>$max==$current, 'disabled'=>false];
        }

        return array(
            'previous' => ['page'=>$previous, 'url'=>$fn($previous), 'active'=>false, 'disabled'=>$previous < 1],
            'next' => ['page'=>$next, 'url'=>$fn($next), 'active'=>false, 'disabled'=>$next > $max],
            'pages' => $pages,
        );
	}


}
