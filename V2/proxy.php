<?php

include_once __DIR__ . '/libs/miniProxy.php';


$ext = strtolower(pathinfo(preg_split('/(\?|#)/',$_SERVER['QUERY_STRING'])[0],PATHINFO_EXTENSION));

if(in_array($ext,array('js','css','png','jpg','jpeg','gif'))){
    die();
}



?>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>


<style>
    *{
        cursor: pointer !important;
    }

    header,footer,iframe{
        display: none !important;
    }

    .hovered{
        background-color: rgba(59, 80, 177, 0.71) !important;
    }
</style>

<script type="text/javascript">

    var is_clicked = false;

    var infos;

    $('*').hover(function(e){
        $('*').removeClass('hovered');


        var target = $(e.target);

        target.addClass('hovered');
    })
    .off('click').on('click',function(e){
        e.preventDefault();


        if(is_clicked){
            return;
        }

        is_clicked = true;


        setTimeout(function(){
            is_clicked = false;
        },200);

        var target = $(e.target);


         infos = {
           // _html : target.html(),
           // _text : target.text().trim(),
             _tag : target.prop("tagName").toLowerCase(),
            _class :  '.' + target.attr('class').trim().replace('hovered','').replace(/ /g,'.'),
            _url : target.find('a').length ? target.find('a').attr('href').replace(/(.+proxy\.php\?)/g,'') : '',
            _parent_id : target.closest('[id]').attr('id'),
             getCssSelector : function(){
                 return this._tag + this._class.substring(0,this._class.length - 1);
             }
        };


        if(window.parent && typeof window.parent.onIframeClick == 'function' ){
            window.parent.onIframeClick();
        }

        console.log(infos);

    })

</script>
