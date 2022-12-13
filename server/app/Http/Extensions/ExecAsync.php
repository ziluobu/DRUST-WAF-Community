<?php

namespace App\Http\Extensions;


class ExecAsync
{

    public function __construct($cmd)
    {
        $this->cmd             = $cmd;
        $this->cacheFile       = base_path('sh/').".cache-pipe-".uniqid();
        $this->statusCacheFile = base_path('sh/').".status-cache-pipe-".uniqid();
        $this->lineNumber      = 0;
    }


    public function hasFinished()
    {
        //说明没有成功
        if (!file_exists($this->statusCacheFile)) {
            if (!file_exists($this->cacheFile)) {
                $this->lineNumber++;
                if ($this->lineNumber > 10) {
                    return '执行系统命令超时';
                }
                return 'waiting';
            }
            $fp = fopen($this->cacheFile, 'r');
            while ($buf = fgets($fp)) {
                $res = $buf;
            }
            @unlink($this->cacheFile);
            @unlink($this->statusCacheFile);
            fclose($fp);
            return $res;
        } else {
            @unlink($this->cacheFile);
            @unlink($this->statusCacheFile);
            return 'success';
        }
    }


    public function run()
    {
        if ($this->cmd) {
            //$out = exec('{ '.$this->cmd." > ".$this->cacheFile." && echo finished > ".$this->statusCacheFile.";} > /dev/null 2>/dev/null &");
            $handle = popen($this->cmd." 2> ".$this->cacheFile." && echo finished > ".$this->statusCacheFile, 'r');
            pclose($handle);
            //$out = exec('{ '.$this->cmd." > ".$this->cacheFile." && echo finished > ".$this->statusCacheFile.";} > /dev/null 2>/dev/null &");
        }
    }
}
