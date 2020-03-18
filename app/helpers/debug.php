<?php

function debug($v, $msg=null, $exit=false, $prettify = true) 
{			
    if (gettype($v)=='boolean'){
        echo ($v ? "TRUE" : "FALSE");
    }	

    if (php_sapi_name() == 'cli')
    {
        if ($msg!="")
            echo $msg."\n";

        print_r($v);	
    }else{	
        if ($msg!="")
            echo $msg."<br/>";
        
        if ($prettify){
            print '<pre>';
            print_r($v);
            print '</pre>';
        }else
            print_r($v);	
    }
    
    if ($exit)		
        exit;				
}		

