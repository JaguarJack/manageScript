<?php
namespace Core\Cen;

use SplFileObject;
use FilesystemIterator;
use Core\Cen\Config;

class File
{ 
    //文件操作句柄
    private $handle;
    
    public function open($file_name, $open_mode = 'w')
    {
        $this->handle = new SplFileObject($file_name, $open_mode);

        return $this;
    }
    
    /**
     * @description:写入
     * @author wuyanwen(2017年7月18日)
     * @param unknown $message
     * @param unknown $lock
     */
    public function write($message, $lock = 0) 
    {
        if (!$lock)
            return $this->handle->fwrite($message);

        //获取独占锁
        $this->handle->flock($lock);
        $this->handle->fwrite($message);
        //释放锁资源
        $this->handle->flock(LOCK_UN);
    }
    
    /**
     * @description:读取文件内容
     * @author wuyanwen(2017年7月18日)
     * @param unknown $length
     */
    public function read($length)
    {
        $content = $this->handle->fread($length);
        return $content === false ? false : $content;
    }
    
    /**
     * @description:文件是否存在
     * @author wuyanwen(2017年7月18日)
     * @param unknown $path
     * @return boolean
     */
    public function exists($path)
    {
        return file_exists($path) ? true : false;
    }
    
    /**
     * @description:创建目录
     * @author wuyanwen(2017年7月18日)
     * @param unknown $directory
     * @param number $perm
     */
    public function mkDirectory($directory, $perm = 0755, $rev = true)
    {
        return mkdir($directory, $perm, $rev);
    }
    
    /**
     * @description:匹配目录下的文件
     * @author wuyanwen(2017年7月18日)
     * @param unknown $path
     */
    public function glob($pattern, $flag = '*')
    {
        return glob($pattern);
    }
    /**
     * @description:目录是否存在
     * @author wuyanwen(2017年7月18日)
     * @param unknown $path
     * @return boolean
     */
    public function isDirectory($path)
    {
        return is_dir($path);
    }
    
    /**
     * @description:删除文件
     * @author wuyanwen(2017年7月18日)
     * @param unknown $path
     */
    public  function delete($paths)
    {
        $paths = is_array($paths) ? $paths : [$paths];
        $success = true;
        
        foreach ($paths as $path) {
            if (! @unlink($path)) 
                $success = false;
                        
            if ($success === false) 
                return $success;
        }
        
        return $success;
    }
    
    /**
     * @description:是否是文件
     * @author wuyanwen(2017年7月18日)
     * @param unknown $file
     */
    public function isFile($file)
    {
        return is_file($file);
    }
    
    /**
     * @description:文件大小
     * @author wuyanwen(2017年7月18日)
     * @param unknown $file
     * @return number
     */
    public function fileSize($file)
    {
        return filesize($file);
    }
    
    /**
     * @description:获取文件后缀
     * @author wuyanwen(2017年7月18日)
     * @param unknown $file
     * @return mixed
     */
    public function extension($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }
    
    /**
     * @description:获取文件名
     * @author wuyanwen(2017年7月18日)
     * @param unknown $file
     * @return mixed
     */
    public function baseName($file)
    {
        return pathinfo($file, PATHINFO_BASENAME);
    }
    
    /**
     * @description:获取dirname
     * @author wuyanwen(2017年7月18日)
     * @param unknown $file
     * @return mixed
     */
    public function dirName($file)
    {
        return pathinfo($file, PATHINFO_DIRNAME);
    }
    
    /**
     * @description:返回文件类型
     * @author wuyanwen(2017年7月18日)
     * @param unknown $file
     * @return string
     */
    public function filetype($file)
    {
        return filetype($file);
    }
    
    /**
     * @description:删除目录
     * @author wuyanwen(2017年7月18日)
     * @param unknown $directory
     */
    public function deleteDirectory($directory, $is_delete_directory = false)
    {
        if (!$this->isDirectory($directory))
            return false;
        
        $fileIterator = new FilesystemIterator($directory);
        
        foreach ($fileIterator as $file) {
            //删除子目录
            if ($file->isDir() && ! $file->isLink()) {
                $this->deleteDirectory($file->getPathname());
            }
            else {
                $this->delete($file->getPathname());
            }
        }
        //删除mul
        if (!$is_delete_directory) 
            @rmdir($directory);
        
        return true;
    }
    
    /**
     * @description:清除目录
     * @author wuyanwen(2017年7月18日)
     */
    public function clearDirectory($directory)
    {
        return $this->deleteDirectory($directory, true);
    }
    
    /**
     * @description:include file
     * @author wuyanwen(2017年7月19日)
     */
    public function includeFile($file)
    {
        return include_once $file;
    }
    
    /**
     * @description:require file
     * @author wuyanwen(2017年7月19日)
     */
    public function requireFile($file)
    {
        return require_once $file;
    }
}