<html>
    <head>
        <style>
            a:link, a:visited {
                background-color: #fff;
                border-color: #a2a2a2;
                color: #24A0D8;
                text-decoration: none;
                font-weight: normal;
            }
            #banner div {
                background-color: #fff;
                color: #000;
                margin-bottom: 10px;
            }

            .header {
                border-top: 1px dotted #B9BBBD;
                font-size: 1.8em;
                font-family: Georgia Regular,Georgia,garamond pro,garamond,times new roman,times,serif,droid sans;
                padding: 15px 10px;
            }

            .lead {
                font-weight: 400;
                font-family: Georgia Regular,Georgia,garamond pro,garamond,times new roman,times,serif;
                padding: 10px 10px;
                font-size: 18px;
                color: #000;
                line-height: 23px;
                color: #333;
            }
        </style>
    </head>
    <body style="background-color: #e3e5e3;font-family: Arial, 'Arial Unicode MS', Verdana, sans-serif;">
        <div style="margin: 0 auto;background: #fff;width: 90.90909091%;max-width: 986px;overflow: auto;padding: 10px 0px;">
            <div style="overflow: auto;padding: 0px 10px;">
                <div style="float:left;">
                    <div id="logo">
                        <a href="<?php echo trans('messages.kthlink')?>">
                            <img src="https://apps.lib.kth.se/mrbs/images/KTH_Logotyp_RGB_2013-2.svg" alt="KTH">
                        </a>
                    </div>
                </div>
                <div id="banner">
                    <div style="float:right;width: 100%;text-align: right;" id="more_info">
                        <ul style="list-style-type: none;margin: 0;padding: 0;font-size: 14px;">
                            <li style="display: inline-block;"><a href="https://apps.lib.kth.se/mrbssandbox/<?php echo $view?>.php?area=<?php echo $area_id?>"><?php echo trans('messages.home')?></a></li> |
                            <li style="display: inline-block;"><a href="<?php echo trans('messages.kthblink')?>"><?php echo trans('messages.kthb')?></a></li> | 
                            <li style="display: inline-block;">
                                <a style="color: #24A0D8;" href="https://apps.lib.kth.se/mrbssandbox/search.php?advanced=0&datatable=0&search_str=&day=&month=&year=&area=<?php echo $area_id?>&datatable=1"><?php echo trans('messages.mybookings')?></a>
                            </li> | 
                            <li style="display: inline-block;">
                                <a style="color: #24A0D8;" href="https://apps.lib.kth.se/mrbs/help.php"><?php echo trans('messages.help')?></a>
                            </li>
                        </ul>
                    </div>
                    <input type="hidden" name="datatable" value="1">
                </div> 	
            </div>
            <div class="header">
                <span><?php echo trans('messages.confirmheader')?></span>
            </div>
            <div class="lead"><?php echo trans('messages.confirmmessage_1') . $name . trans('messages.confirmmessage_2') . date('Y-m-d H:i',$start_time) . '-' . date('H:i',$end_time) . trans('messages.confirmmessage_3');?></div>
        </div>
    </body>
</html>