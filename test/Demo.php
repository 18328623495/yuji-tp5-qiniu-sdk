<?php


class Demo
{
    public function test(){
        $qiniu=new Qiniu();
        return  $qiniu->fetch('http://xx.xx.xx');
    }
}
