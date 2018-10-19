<html>
    <head>
        <style>
            body {
                background-color: #e3e5e3;
                font-family: Arial, 'Arial Unicode MS', Verdana, sans-serif;
            }

            .page {
                margin: 0 auto;
                background: #fff;
                width: 90.90909091%;
                max-width: 986px;
                overflow: auto;
                padding: 10px 0px;
            }

            .top {
                overflow: auto;
                padding: 0px 10px;
            }

            .logo {
                float:left;
            }

            .menu div {
                background-color: #fff;
                color: #000;
                margin-bottom: 10px;
                float:right;
                width: 100%;
                text-align: right;
            }

            ul {
                list-style-type: none;
                margin: 0;
                padding: 0;
                font-size: 14px;
            }

            li {
                display: inline-block;
            }

            a {
                color: #24A0D8;
            }

            a:link, a:visited {
                background-color: #fff;
                border-color: #a2a2a2;
                color: #24A0D8;
                text-decoration: none;
                font-weight: normal;
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

            @media (max-width: 599px) and (min-width: 200px) {
	            .page {
		            width: 100%;
	            }
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="top">
                <div class="logo">
                    <div id="logo">
                        <a href="<?php echo trans('messages.kthlink')?>">
                            <img src="<?php echo env("MRBS_URL") . env("DB_DATABASE")?>/images/KTH_Logotyp_RGB_2013-2.svg" alt="KTH">
                        </a>
                    </div>
                </div>
                <div class="menu">
                    <div id="more_info">
                        <ul>
                            <li><a href="<?php echo env("MRBS_URL") . env("DB_DATABASE")?>/<?php echo $view?>.php?area=<?php echo $area_id?>"><?php echo trans('messages.home')?></a></li> |
                            <li><a href="<?php echo trans('messages.kthblink')?>"><?php echo trans('messages.kthb')?></a></li> | 
                            <li>
                                <a href="<?php echo env("MRBS_URL") . env("DB_DATABASE")?>/search.php?advanced=0&datatable=0&search_str=&day=&month=&year=&area=<?php echo $area_id?>&datatable=1"><?php echo trans('messages.mybookings')?></a>
                            </li> | 
                            <li>
                                <a href="<?php echo env("MRBS_URL") . env("DB_DATABASE")?>/help.php"><?php echo trans('messages.help')?></a>
                            </li>
                        </ul>
                    </div>
                    <input type="hidden" name="datatable" value="1">
                </div> 	
            </div>
            <div class="header">
                <span><?php echo trans('messages.confirmheader')?></span>
            </div>
            <?php if ($confirmation){ ?>
                <div class="lead"><?php echo trans('messages.confirmmessage_1') . $name . trans('messages.confirmmessage_2') . date('Y-m-d H:i',$start_time) . '-' . date('H:i',$end_time) . trans('messages.confirmmessage_3');?></div>
            <?php } else { ?>
                <div class="lead"><?php echo $message;?></div>
            <?php } ?>
        </div>
    </body>
</html>