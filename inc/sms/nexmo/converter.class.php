<?php
/**
* 名称:converter
* 
* 使用:
*         $Conv=new converter;
*         $String='GB2312转BIG(简转繁体)';
*         $String=$Conv->convert('GB','BIG',$String);
*         $String='BIG转GB2312(繁转简体);
*         $String=$Conv->convert('BIG','GB',$String);
*         $String='GBK转UTF';
*         $String=$Conv->convert('GBK','UTF',$String);
*         $String='BIG转UTF';
*         $String=$Conv->convert('BIG','UTF',$String);
*         $String='UTF转GBK';
*         $String=$Conv->convert('UTF','GBK',$String);
*         $String='UTF转BIG';
*         $String=$Conv->convert('UTF','BIG',$String);
*/
class converter{

    var $ICONV;//是否可用iconv函数
    var $TablePath;//码表路径
    var $UseMemSize;//把码表读到内存,加快速度,但需要占用更多内存
    /**
     * 构造函数
     * 初始化变量
     */
    function converter($TablePath=null){
        $this->TablePath=empty($TablePath)?dirname(__FILE__).'/tables/':$TablePath;
        if(function_exists('iconv')){
            $this->ICONV=true;
        }
    }
    
    /**
     * 转换函数
     */
    function convert($Source,$Target,$String){
        $Source=strtolower($Source);
        $Target=strtolower($Target);
        $Func=$Source.'2'.$Target;
        return $this->$Func($String);
    }

    /**
     * unicode转utf
     * 详细可见
     * http://www.linuxforum.net/books/UTF-8-Unicode.html
     */
    function uni2utf($Char){
        $Return='';
        if($Char<0x80){
            $Return.=$Char;
        }elseif($Char<0x800){
            $Return.=chr(0xC0|$Char>>6);
            $Return.=chr(0x80|$Char&0x3F);
        }elseif($Char<0x10000){
            $Return.=chr(0xE0|$Char>>12);
            $Return.=chr(0x80|$Char>>6&0x3F);
            $Return.=chr(0x80|$Char&0x3F);
        }elseif($Char<0x200000){
            $Return.=chr(0xF0|$Char>>18);
            $Return.=chr(0x80|$Char>>12&0x3F);
            $Return.=chr(0x80|$Char>>6&0x3F);
            $Return.=chr(0x80|$Char&0x3F);
        }
        return $Return;
    }

    /**
     * utf8转unicode
     */
    function utf2uni($Char){
        switch(strlen($Char)){
            case 1:
                return ord($Char);
            case 2:
                $OutStr=(ord($Char[0])&0x3f)<<6;
                $OutStr+=ord($Char[1])&0x3f;
                return $OutStr;
            case 3:
                $OutStr=(ord($Char[0])&0x1f)<<12;
                $OutStr+=(ord($Char[1])&0x3f)<<6;
                $OutStr+=ord($Char[2])&0x3f;
                return $OutStr;
            case 4:
                $OutStr=(ord($Char[0])&0x0f)<<18;
                $OutStr+=(ord($Char[1])&0x3f)<<12;
                $OutStr+=(ord($Char[2])&0x3f)<<6;
                $OutStr+=ord($Char[3])&0x3f;
                return $OutStr;
        }
    }

    /**
     * 中文互转
     * bg转big
     * 或者big转bg
     * 备注:GB2312是GBK的子集
     */
    function chs2chs($String,$Target,$Type){
        if($Type=='GB' && $this->ICONV) return iconv('GBK','UTF-8',$String);
        if($Type=='BIG' && $this->ICONV) return iconv('BIG5','UTF-8',$String);
        $TableFile=$this->TablePath.($Target=='GB'?'BIG2GB.Table':'GB2BIG.Table');
        if(!file_exists($TableFile)) return false;
        $MapTable=($Type=='MEM'?file_get_contents($TableFile):fopen($TableFile,'rb'));
        $StringLenth=strlen($String);
        $ReturnStr='';
        for($Foo=0;$Foo<$StringLenth;$Foo++){
            if(ord(substr($String,$Foo,1))>127){
                $Str=substr($String,$Foo,2);
                $High=ord($Str[0]);
                $Low=ord($Str[1]);
                $MapAddr=(($High-160)*510)+($Low-1)*2;
                if($Type=='MEM'){
                    $High=$MapTable[$MapAddr];
                    $Low=$MapTable[$MapAddr+1];
                }else{
                    fseek($MapTable,$MapAddr);
                    $High=fgetc($MapTable);
                    $Low=fgetc($MapTable);
                }
                $ReturnStr.="$High$Low";
                $Foo++;
            }else{
                $ReturnStr.=$String[$Foo];
            }
        }
        $Type=='MEM'?null:fclose($MapTable);
        return $ReturnStr;
    }

    /**
     * 汉字转拼音
     * 由于码表是使用GBK的所以同样适用于GB2312
     * 此函数来自Higthman的汉字转拼音
     * 详细见
     * http://www.hightman.cn/demo/getpy.php?source
     */
    Function GBK2PINYIN($String,$Type='File'){
        $TableFile=$this->TablePath.'GBK2PY.Table';
        if(!file_exists($MapFile)){
            return false;
        }
        $MapTable=($Type=='MEM'?file_get_contents($TableFile):$MapTable=fopen($TableFile,'rb'));
        $StringLenth=strlen($String);
        $ReturnStr='';
        for($Foo=0;$Foo<$StringLenth;$Foo++){
            $Char=ord(substr($String,$Foo,1));
            if($Char>127){
                $Str=substr($String,$Foo,2);
                $High=ord($Str[0])-129;
                $Low=ord($Str[1])-64;
                $Addr=($High<<8)+$Low-($High*64);
                if($Addr<0){
                    $ReturnStr.='_';
                }else{
                    $MapAddr=$Addr*8;
                    if($Type=='MEM'){
                        $MapStr='';
                        for($Tmp=0;$Tmp<8;$Tmp++){
                            $MapStr.=$MapTable[($MapAddr+$Tmp)];
                        }
                        $BinStr=unpack('a8py',$MapStr);
                    }else{
                        fseek($MapTable,$MapAddr,SEEK_SET);
                        $BinStr=unpack('a8py',fread($MapTable,8));
                    }
                    $Foo++;
                    $ReturnStr.=$BinStr['py'];
                }
            }else{
                $ReturnStr.=$String[$Foo];
            }
        }
        $Type=='MEM'?null:fclose($MapTable);
        return $ReturnStr;
    }

    /**
     * GBK转UNI
     * GBK转UTF8根据uni2utf得出
     * 此代码来自于
     * http://www.wensh.net/archive.php/topic/287.html
     */
    function chs2uni($String,$Source='GBK',$Target='UTF',$Type='File'){
        if($Source=='GBK' && $this->ICONV){
            return iconv('GBK','UTF-8',$String);
        }
        if($Source=='BIG' && $this->ICONV){
            return iconv('BIG5','UTF-8',$String);
        }
        $MapFile=$this->TablePath;
        $MapFile.=($Source=='GBK'?'GBK2UNI.Table':'BIG2UNI.Table');
        if(!file_exists($MapFile)){
            return false;
        }
        if($Type=='File'){
            $MapTable=fopen($MapFile,'rb');
            $Tmp=fread($MapTable,2);
            $MapSize=ord($Tmp[0])+256*ord($Tmp[1]);
        }else{
            $MapTable=file_get_contents($MapFile);
            $MapSize=ord($MapTable[0])+256*ord($MapTable[1]);
        }
        $ReturnStr='';
        $StringLenth=strlen($String);
        for($Foo=0;$Foo<$StringLenth;$Foo++){
            if(ord($String[$Foo])>127){
                $Str=substr($String,$Foo,2);
                $StrEncode=hexdec(bin2hex($Str));
                $SearchStart=1;
                $SearchEnd=$MapSize;
                while($SearchStart<$SearchEnd-1){
                    $SearchMid=floor(($SearchStart+$SearchEnd)/2);
                    $MapAddr=4*($SearchMid-1)+2;
                    if($Type=='MEM'){
                        $MapEncode=ord($MapTable[$MapAddr])+256*ord($MapTable[$MapAddr+1]);
                    }else{
                        fseek($MapTable,$MapAddr);
                        $TmpStr=fread($MapTable,2);
                        $MapEncode=ord($TmpStr[0])+256*ord($TmpStr[1]);
                    }
                    if($StrEncode==$MapEncode){
                        $SearchStart=$SearchMid;
                        break;
                    }
                    $StrEncode>$MapEncode?$SearchStart=$SearchMid:$SearchEnd=$SearchMid;
                }
                $MapAddr=2+4*($SearchStart-1);
                if($Type=='MEM'){
                    $Encode=ord($MapTable[$MapAddr])+256*ord($MapTable[$MapAddr+1]);
                }else{
                    fseek($MapTable,$MapAddr);
                    $TmpStr=fread($MapTable,2);
                    $Encode=ord($TmpStr[0])+256*ord($TmpStr[1]);
                }
                if($StrEncode==$Encode){
                    if($Type=='MEM'){
                        $StrUni=ord($MapTable[$MapAddr+2])+256*ord($MapTable[$MapAddr+3]);
                    }else{
                        $TmpStr=fread($MapTable,2);
                        $StrUni=ord($TmpStr[0])+256*ord($TmpStr[1]);
                    }
                    $ReturnStr.=$Target=='UTF'?$this->uni2utf($StrUni):$StrUni;
                }else{
                    $ReturnStr.='__';
                }
                $Foo++;
            }else{
                $ReturnStr.=$String[$Foo];
            }
        }
        $Type=='MEM'?null:fclose($MapTable);
        return $ReturnStr;
    }

    /**
     * utf转gbk
     */
    function utf2chs($String,$Target='GBK',$Type='File'){
        if($Source=='GBK' && $this->ICONV){
            return iconv('UTF-8','GBK',$String);
        }
        if($Source=='BIG' && $this->ICONV){
            return iconv('UTF-8','BIG5',$String);
        }
        $MapFile=$this->TablePath.($Target=='GBK'?'UNI2GBK.Table':'UNI2BIG.Table');
        if(!file_exists($MapFile)){
            return false;
        }
        if($Type=='File'){
            $MapTable=fopen($MapFile,'rb');
            $Tmp=fread($MapTable,2);
            $MapSize=ord($Tmp[0])+256*ord($Tmp[1]);
        }else{
            $MapTable=file_get_contents($MapFile);
            $MapSize=ord($MapTable[0])+256*ord($MapTable[1]);
        }
        $ReturnStr='';
        $StringLenth=strlen($String);
        for($Foo=0;$Foo<$StringLenth;$Foo++){
            if(ord($String[$Foo])>127){
                $StrEncode=$this->UTF2UNI(substr($String,$Foo,3));
                $SearchStart=1;
                $SearchEnd=$MapSize;
                while($SearchStart<$SearchEnd-1){
                    $SearchMid=floor(($SearchStart+$SearchEnd)/2);
                    $MapAddr=4*($SearchMid-1)+2;
                    if($Type=='MEM'){
                        $MapEncode=ord($MapTable[$MapAddr])+256*ord($MapTable[$MapAddr+1]);
                    }else{
                        fseek($MapTable,$MapAddr);
                        $TmpStr=fread($MapTable,2);
                        $MapEncode=ord($TmpStr[0])+256*ord($TmpStr[1]);
                    }
                    if($StrEncode==$MapEncode){
                        $SearchStart=$SearchMid;
                        break;
                    }
                    $StrEncode>$MapEncode?$SearchStart=$SearchMid:$SearchEnd=$SearchMid;
                }
                $MapAddr=2+4*($SearchStart-1);
                if($Type=='MEM'){
                    $Encode=ord($MapTable[$MapAddr])+256*ord($MapTable[$MapAddr+1]);
                }else{
                    fseek($MapTable,$MapAddr);
                    $TmpStr=fread($MapTable,2);
                    $Encode=ord($TmpStr[0])+256*ord($TmpStr[1]);
                }
                if($StrEncode==$Encode){
                    if($Type=='MEM'){
                        $Low=$MapTable[$MapAddr+2];
                        $High=$MapTable[$MapAddr+3];
                    }else{
                        $TmpStr=fread($MapTable,2);
                        $High=$TmpStr[1];
                        $Low=$TmpStr[0];
                    }
                    $ReturnStr.="$High$Low";
                }else{
                    $ReturnStr.='__';
                }
                $Foo=$Foo+2;
            }else{
                $ReturnStr.=$String[$Foo];
            }
        }
        $Type=='MEM'?null:fclose($MapTable);
        return $ReturnStr;
    }
    
    function gb2big($String){
        return strlen($String)<$this->UseMemSize?$this->chs2chs($String,'BIG','File'):$this->chs2chs($String,'BIG','MEM');
    }

    function big2gb($String){
        return strlen($String)<$this->UseMemSize?$this->chs2chs($String,'GB','File'):$this->chs2chs($String,'GB','MEM');
    }

    function gbk2py($String){
        return strlen($String)<$this->UseMemSize?$this->gbk2py($String,'File'):$this->gbk2py($String,'MEM');
    }

    function gbk2utf($String){
        return strlen($String)<$this->UseMemSize?$this->chs2uni($String,'GBK','UTF','File'):$this->chs2uni($String,'GBK','UTF','MEM');
    }

    function big2utf($String){
        return strlen($String)<$this->UseMemSize?$this->chs2uni($String,'BIG','UTF','File'):$this->chs2uni($String,'BIG','UTF','MEM');
    }

    function utf2gbk($String){
        return strlen($String)<$this->UseMemSize?$this->utf2chs($String,'GBK','File'):$this->utf2chs($String,'GBK','MEM');
    }

    function utf2big($String){
        return strlen($String)<$this->UseMemSize?$this->utf2chs($String,'BIG','File'):$this->utf2chs($String,'BIG','MEM');
    }
}
?>