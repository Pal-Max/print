<?php

// ===================================================================
// | FileName:      /Print/Printer/FileController.class.php
// ===================================================================
// | Discription：   FileController 文件管理控制器
//      <命名规范：>
// ===================================================================
// +------------------------------------------------------------------
// | 云印南开
// +------------------------------------------------------------------
// | Copyright (c) 2014 云印南开团队 All rights reserved.
// +------------------------------------------------------------------

/**
 * Class and Function List:
 * Function list:
 * - index()
 * - refresh()
 * - set()
 * - _empty()
 * Classes list:
 * - FileController extends Controller
 */
namespace Printer\Controller;
use Think\Controller;
class FileController extends Controller
{
	
	/**
	 *index()
	 *打印店文件列表
	 *@param $status 状态，为空全部显示
	 */
	public function index() 
	{
		$pid    = pri_id(U('Index/index'));
		if ($pid) 
		{
			$condition['pri_id']        = $pid;
//				$cache_key   = cache_name('printer', $pid);
		    $condition['status']             = array('between', '1,4');
			$this->assign('history', false);
			$this->title = '打印任务列表';
			$File        = D('FileView');
			$ppt_layout = C('PPT_LAYOUT');
			$result  = $File->where($condition)->order('file.id desc')->select();//cache($cache_key, 10)->select();
			if($result)
            {
			    foreach($result as &$file)
                {
                    $file['ppt_layout'] = $ppt_layout[$file['ppt_layout']];
                }
                unset($file);
                $this->data = $result;
			    $this->display();
			 }
			 else
			 {
			    $this->error('no file');
			 }
		} else
		{
			$this->redirect('Printer/Index/index');
		}
	}

	public function history() 
	{
		$pid    = pri_id(U('Index/index'));
		if ($pid) 
		{
			$condition['pri_id']        = $pid;
			$status = 5;
			$condition['status']        = $status;
			$this->assign('history', true);
			$this->title = '已打印文件历史记录';
			$File        = D('FileView');
			$count      = $File->where($condition)->count();
            $Page       = new \Think\Page($count,10);
            $show       = $Page->show();
            $ppt_layout = C('PPT_LAYOUT');
            $result  = $File->where($condition)->order('file.id desc')->limit($Page->firstRow.','.$Page->listRows)->select();//cache($cache_key, 10)->select();
            foreach($result as &$file)
            {
                $file['ppt_layout'] = $ppt_layout[$file['ppt_layout']];
            }
            unset($file);
            $this->data = $result;
            $this->assign('page',$show);
			$this->display();
		} else
		{
			$this->redirect('Printer/Index/index');
		}
	}


	public function refresh() 
	{
		$pid        = pri_id(U('Index/index'));
		if ($pid) 
		{
			$map['pri_id']            = $pid;
			$map['id']            = array('gt', I('file_id', null, 'intval'));
			$map['status']            = array('between', '1,4');
			$File       = D('FileView');
			// $cache_key  = cache_name('printer', $pid);
			$ppt_layout = C('PPT_LAYOUT');
			$result  = $File->where($map)->order('file.id asc')->limit(10)->select();
			//var_dump($map);
			
			if($result)
			{
			    foreach($result as &$file)
                {
                    $file['ppt_layout'] = $ppt_layout[$file['ppt_layout']];
                }
                unset($file);
                $this->success($result);
            }
            else
            {
                $this->error('error');
            }
            
		}
	}
	
	/**
	 *set()
	 *更新文件状态（需要验证文件是否在此点打印）
	 *@param $fid    文件id
	 *@param $status 文件状态
	 */
	public function set() 
	{
		
		$pid    = pri_id(U('Index/index'));
		$fid    = I('fid', null, 'intval'); 
		$File   = M('File');
		$result = $File->where('id="%d"', $fid)->field('status,copies')->find();

	    if($result['status'] == C('FILE_UPLOAD'))    
	    {
	        $status['status'] = C('FILE_DOWNLOAD');
	    }
	    elseif($result['status'] == C('FILE_DOWNLOAD'))
	    {
	        $status['status'] = C('FILE_PRINTED');
	    }
	    elseif($result['status'] == C('FILE_PRINTED'))
	    {
	        $status['status'] = C('FILE_PAID');
	    }
	    
	    if($result['copies']==0&&$result['status'] == C('FILE_UPLOAD'))
	    {
	        $status['operation'] = C('FILE_PRINTED');
	    }
	    else
	    {
	        $status['operation'] = $status['status'];
	    }
	    
	    $result = $File->where('id="%d"', $fid)->cache(true)->setField('status', $status['operation']);
	    if($result)
	    {
           $this->success($status);
        }
	    else
        {	        	
            $this->error('状态不可设置');
	    }
	}
	
	
	public function download()
    {
		$pid    = pri_id(U('Index/index'));
		$fid    = I('fid', null, 'intval');
        $map['pri_id']        = $pid;
        $map['id']        = $fid;
        $map['status']        = array('gt', 0);
        $File   = M('File');
        $info    = $File->where($map)->field('url,status,copies,name')->find();

        if ($info) 
        {
        	if($info['copies']==0)
        	{
        	    $data['operation']= C('FILE_PRINTED');
        	}
        	else
        	{
        	    $data['operation']= C('FILE_DOWNLOAD');
        	}
        	if($info['status']==C('FILE_UPLOAD'))
        	{
        	    $File->where('id=%d',$fid)->setField('status',$data['operation']);
        	}
        	$data['url'] = download($info['url'],'attname='.$info['name']);
            $this->success($data);
        } 
        else
        {
            $this->error('文件已删除，不能再下载！');
        }
    }
    
    public function bind()
    {
        $pid    = pri_id(U('Index/index'));
        if($pid)
        {
            $uid = I('uid');
            $phone = get_phone_by_id($uid);
            if($phone)
            {
                $this->success($phone);
            }
            else
            {
                $this->error('Unknown error');
            }
        }
        else
        {
            $this->error('Not validate');
        }
    }
    
	/**
	 *404页
	 */
	public function _empty() 
	{
		$this->redirect('index');
	}
}
