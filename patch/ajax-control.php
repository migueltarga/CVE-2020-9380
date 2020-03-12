<?php
session_start();
if (file_exists("functions.php")) {
    include_once "functions.php";
    /***
    Vulnerability 1: Arbitrary File Upload

    if (isset($_FILES["logoImage"])) {
        $target_dir = "../images/";
        $target_file = $target_dir . basename($_FILES["logoImage"]["name"]);
        if (move_uploaded_file($_FILES["logoImage"]["tmp_name"], $target_file)) {
            echo "images/" . $_FILES["logoImage"]["name"];
            exit;
        }
        echo "errorImage";
        exit;
    }
    ***/
    if (isset($_POST["action"]) && $_POST["action"] == "SaveSortSettings") {
        setcookie($_SESSION["webTvplayer"]["username"] . "_" . $_POST["SortIN"], $_POST["selectedVal"], time() + 2 * 7 * 24 * 60 * 60, "/", $_SERVER["SERVER_NAME"], false);
        exit;
    }
    if (isset($_POST["action"]) && $_POST["action"] == "SaveSettings") {
        $CookieArray[$_SESSION["webTvplayer"]["username"]] = array("live_player" => $_POST["live_player_val"], "movie_player" => $_POST["movie_player_val"], "series_player" => $_POST["series_player_val"], "epgtimeshift" => $_POST["epgtimeshift_val"], "timeformat" => $_POST["timeformat_val"], "parentenable" => $_POST["parentenable"], "parentpassword" => webtvpanel_baseEncode($_POST["parentmainpassword_val"]), "primaryColor" => $_POST["primaryColor"], "secondryColor" => $_POST["secondryColor"]);
        $CookieArray = json_encode($CookieArray);
        setcookie("settings_array", $CookieArray, time() + 2 * 7 * 24 * 60 * 60, "/", $_SERVER["SERVER_NAME"], false);
        echo "0";
        exit;
    }
    if (isset($_POST["action"]) && $_POST["action"] == "confirm_parentpassword") {
        $ReturnData = 1;
        $LiveSection = "";
        $MovieSection = "";
        $SeriesSection = "";
        $epgtimeshift = "0";
        $timeformat = "12";
        $parentenable = "";
        $parentpassword = "";
        if (isset($_COOKIE["settings_array"])) {
            $SessionStroedUsername = $_SESSION["webTvplayer"]["username"];
            $SettingArray = json_decode($_COOKIE["settings_array"]);
            if (isset($SettingArray->{$SessionStroedUsername}) && !empty($SettingArray->{$SessionStroedUsername})) {
                $LiveSection = $SettingArray->{$SessionStroedUsername}->live_player;
                $MovieSection = $SettingArray->{$SessionStroedUsername}->movie_player;
                $SeriesSection = $SettingArray->{$SessionStroedUsername}->series_player;
                $epgtimeshift = $SettingArray->{$SessionStroedUsername}->epgtimeshift;
                $timeformat = $SettingArray->{$SessionStroedUsername}->timeformat;
                $parentenable = $SettingArray->{$SessionStroedUsername}->parentenable;
                $parentpassword = $SettingArray->{$SessionStroedUsername}->parentpassword;
            }
        }
        $parentPass = $_POST["parentPass"];
        if (webtvpanel_baseDecode($parentpassword) != $parentPass) {
            $ReturnData = 0;
        }
        echo $ReturnData;
        exit;
    }
    if (isset($_POST["action"]) && $_POST["action"] == "StreamDetailsCheck") {
        $returnData = 0;
        $StreamLineUsername = $_POST["unameVal"];
        $StreamLinePassword = $_POST["upassVal"];
        $StreamLineHostUrlVal = $_POST["HostUrlVal"];
        $CheckStreamDetails = webtvpanel_CheckstreamLine($StreamLineUsername, $StreamLinePassword, $StreamLineHostUrlVal);
        echo $CheckStreamDetails;
        exit;
    }
    if (isset($_POST["action"]) && $_POST["action"] == "CheckLicense") {
        $license = $_POST["licenseIsval"];
        $LicenseResponse = webtvpanel_CheckLicense($license);
        echo json_encode($LicenseResponse);
        exit;
    }
    /***
    Vulnerability 2: Code Injection
    
    if (isset($_POST["action"]) && $_POST["action"] == "installation") {
        $response["result"] = "no";
        $content = "<?php \n";
        $content .= "\$XCStreamHostUrl = \"" . $_POST["HostUrlVal"] . "\";" . "\n";
        $content .= "\$XClogoLinkval = \"" . $_POST["logoLinkval"] . "\";" . "\n";
        $content .= "\$XCcopyrighttextval = \"" . $_POST["copyrighttextval"] . "\";" . "\n";
        $content .= "\$XCcontactUslinkval = \"" . $_POST["contactUslinkval"] . "\";" . "\n";
        $content .= "\$XChelpLinkval = \"" . $_POST["helpLinkval"] . "\";" . "\n";
        $content .= "\$XClicenseIsval = \"" . $_POST["licenseIsval"] . "\";" . "\n";
        $content .= "\$XClocalKey = \"" . $_POST["LocalKey"] . "\";" . "\n";
        $content .= "\$XCsitetitleval = \"" . $_POST["sitetitleval"] . "\";" . "\n";
        $content .= "?>";
        if (file_exists("../configuration.php")) {
            unlink("../configuration.php");
        }
        $fp = fopen("../configuration.php", "w");
        fwrite($fp, $content);
        fclose($fp);
        chmod("../configuration.php", 511);
        if (file_exists("../configuration.php")) {
            $response["result"] = "yes";
        }
        echo json_encode($response);
        exit;
    }
    ***/
    if (isset($_POST["action"]) && $_POST["action"] == "webtvlogin") {
        include_once "../configuration.php";
        $bar = "/";
        if (substr($XCStreamHostUrl, -1) == "/") {
            $bar = "";
        }
        $XCStreamHostUrl = $XCStreamHostUrl . $bar;
        $UserName = $_POST["uname"];
        $UserPassword = $_POST["upass"];
        $rememberMe = $_POST["rememberMe"];
        $returnData = array();
        $ApiLinkIs = $XCStreamHostUrl . "player_api.php?username=" . $UserName . "&password=" . $UserPassword;
        $checkLogin = webtvpanel_CallApiRequest($ApiLinkIs);
        $CateGoriesArray = array();
        $Catechanneldata = array();
        $Result = $checkLogin;
        if ($Result["result"] == "success") {
            if (isset($Result["data"]->user_info->auth)) {
                if ($Result["data"]->user_info->auth != 0) {
                    if ($Result["data"]->user_info->status == "Active") {
                        if ($rememberMe == "on") {
                            setcookie("username", $UserName, time() + 2 * 7 * 24 * 60 * 60, "/", $_SERVER["SERVER_NAME"], false);
                            setcookie("userpassword", base64_encode($UserPassword), time() + 2 * 7 * 24 * 60 * 60, "/", $_SERVER["SERVER_NAME"], false);
                        }
                        $SessionArray = array("username" => $Result["data"]->user_info->username, "password" => $Result["data"]->user_info->password, "auth" => $Result["data"]->user_info->auth, "status" => $Result["data"]->user_info->status, "exp_date" => $Result["data"]->user_info->exp_date, "active_cons" => $Result["data"]->user_info->active_cons, "is_trial" => $Result["data"]->user_info->is_trial, "max_connections" => $Result["data"]->user_info->max_connections, "created_at" => $Result["data"]->user_info->created_at, "allowed_output_formats" => $Result["data"]->user_info->allowed_output_formats, "url" => $Result["data"]->server_info->url, "port" => $Result["data"]->server_info->port, "rtmp_port" => $Result["data"]->server_info->rtmp_port, "timezone" => $Result["data"]->server_info->timezone);
                        $_SESSION["webTvplayer"] = $SessionArray;
                        $returnData = array("result" => "success", "message" => $SessionArray);
                    } else {
                        $returnData = array("result" => "error", "message" => "Status is " . $Result["data"]->user_info->status);
                    }
                } else {
                    $returnData = array("result" => "error", "message" => "Invalid Details");
                }
            } else {
                $returnData = array("result" => "error", "message" => "Invalid Details");
            }
        } else {
            $returnData = array("result" => "error", "message" => $Result["data"]);
        }
        echo json_encode($returnData);
    }
    if (isset($_POST["action"]) && $_POST["action"] == "CheckLicense") {
        $ReturnData = 0;
        $license = $_POST["licenseIsval"];
        $LicenseResponse = livestreaming_CheckLicense($license);
        echo json_encode($LicenseResponse);
        exit;
    }
    if (isset($_POST["action"]) && $_POST["action"] == "logoutProcess") {
        unset($_SESSION["webTvplayer"]);
        session_destroy();
    }
    if (isset($_POST["action"]) && $_POST["action"] == "CheckSerVerUrl") {
        $ApiLinkIs = $_POST["HostUrlVal"];
        $http = curl_init($ApiLinkIs);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 0);
        $result = curl_exec($http);
        $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
        curl_close($http);
        echo $http_status;
        exit;
    }
    if (isset($_POST["action"]) && $_POST["action"] == "getMoviesDataFromCategoryId") {
        $counter = 0;
        $hostURL = $_POST["hostURL"];
        $username = $_SESSION["webTvplayer"]["username"];
        $password = $_SESSION["webTvplayer"]["password"];
        $dataoffset = isset($_POST["dataoffset"]) ? $_POST["dataoffset"] : 0;
        $datalimit = isset($_POST["datalimit"]) ? $_POST["datalimit"] : 28;
        $bar = "/";
        if (substr($hostURL, -1) == "/") {
            $bar = "";
        }
        $ApiLink = $hostURL . $bar . "player_api.php?username=" . $username . "&password=" . $password . "&action=get_vod_streams&category_id=" . $_POST["categoryID"];
        $ApiData = webtvpanel_CallApiRequest($ApiLink);
        if ($ApiData["result"] == "success") {
            $SessionStroedUsername = $_SESSION["webTvplayer"]["username"];
            $MoviesSortingType = isset($_COOKIE[$SessionStroedUsername . "_movies"]) && !empty($_COOKIE[$SessionStroedUsername . "_movies"]) ? $_COOKIE[$SessionStroedUsername . "_movies"] : "default";
            if ($MoviesSortingType != "default") {
                $Arrayforsorting = array();
                foreach ($ApiData["data"] as $StreamData) {
                    $Arrayforsorting[$MoviesSortingType == "topadded" ? $StreamData->added : $StreamData->name] = (object) array("num" => $StreamData->num, "name" => $StreamData->name, "stream_type" => $StreamData->stream_type, "stream_id" => $StreamData->stream_id, "stream_icon" => $StreamData->stream_icon, "rating" => $StreamData->rating, "rating_5based" => $StreamData->rating_5based, "added" => $StreamData->added, "category_id" => $StreamData->category_id, "container_extension" => $StreamData->container_extension, "custom_sid" => $StreamData->custom_sid, "direct_source" => $StreamData->direct_source);
                }
                array_multisort(array_keys($Arrayforsorting), SORT_NATURAL | SORT_FLAG_CASE, $Arrayforsorting);
                if ($MoviesSortingType == "desc") {
                    $Arrayforsorting = array_reverse($Arrayforsorting, false);
                }
                $ApiData["data"] = $Arrayforsorting;
            }
            $TotalResultFound = count($ApiData["data"]);
            foreach ($ApiData["data"] as $data) {
                $Icon = $data->stream_icon;
                if ($Icon == "") {
                    $Icon = "";
                }
                $counter++;
                if ($dataoffset <= $counter && $counter <= $datalimit) {
                    $QuersyData = "moviename=" . $data->name . "&StreamId=" . $data->stream_id . "&CateGoryId=" . $data->category_id . "&posterImage=" . $Icon . "&extension=" . $data->container_extension . "&rating5=" . $data->rating_5based;
                    echo "\t\t\t\t<li class=\"thumb-b animate streamList rippler rippler-default sectionNo";
                    echo $data->stream_id;
                    echo " un-";
                    echo $counter;
                    echo "\" onclick=\"showInfo('";
                    echo $data->stream_id;
                    echo "')\" data-streamID=\"";
                    echo $data->stream_id;
                    echo "\">\r\n\t\t\t\t\t<input type=\"hidden\" class=\"serch_key\" value=\"";
                    echo $data->name;
                    echo "\" data-parentliclass=\"sectionNo";
                    echo $data->stream_id;
                    echo "\">\r\n\r\n\t\t\t\t\t<input type=\"hidden\" id=\"fullData-";
                    echo $data->stream_id;
                    echo "\" value=\"";
                    echo webtvpanel_baseEncode($QuersyData);
                    echo "\">\r\n\t\t\t\t\t<!-- <h2 class=\"WihthoutZoom \">";
                    echo $data->name;
                    echo "</h2> -->\r\n\t\t\t\t\t<div class=\"view view-tenth rippler rippler-default\"><img class=\"iconImage rippler rippler-img rippler-bs-primary\" src=\"";
                    echo $Icon;
                    echo "\" alt=\"\" onerror=\"this.src='images/no-poster.jpg';this.parentElement.className='view view-tenth showEI ';\" >\r\n\t\t\t\t\t\t<div class=\"mask \">\r\n\t\t\t\t\t\t\t<h2 >";
                    echo $data->name;
                    echo "</h2>\r\n\t\t\t\t\t\t\t<!-- <div class=\"fav\"> <a href=\"#\"><span class=\"fa fa-heart-o fa-heart\"></span></a> <span>";
                    echo $data->rating_5based;
                    echo "</span> </div> -->\r\n\t\t\t\t\t\t\t<div class=\"n-ratting\"> \r\n\t\t\t\t\t\t\t";
                    $ratingData = $data->rating_5based;
                    $rating = webtvpanel_starRating($ratingData);
                    echo "\r\n\t\t\t\t\t\t\t<!-- <span class=\"fa fa-star\"></span> <span class=\"fa fa-star\"></span> <span class=\"fa fa-star\"></span> <span class=\"fa fa-star\"></span> <span class=\"fa fa-star-o\"></span> --> </div>\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</li>\r\n\t\t\t\t\r\n\t\t\t\t";
                }
            }
            if ($datalimit < $counter) {
                echo "\t\t\t<center class=\"loading-loadBtn\">\r\n\t\t\t\t<button class=\"LoadMoreBtn btn btn-success rippler rippler-default\" data-dataoffset=\"";
                echo $datalimit;
                echo "\" data-categoryID=\"";
                echo $_POST["categoryID"];
                echo "\">Load More <i class=\"LoadingMoreFa fa fa-spin fa-spinner hideOnload\"></i></button>\r\n\t\t\t</center>\r\n\t\t\t";
            }
            exit;
        } else {
            echo "0";
            exit;
        }
    } else {
        if (isset($_POST["action"]) && $_POST["action"] == "GetSeriesByCateGoryId") {
            $CateGoryId = $_POST["categoryID"];
            $counter = 0;
            $hostURL = $_POST["hostURL"];
            $username = $_SESSION["webTvplayer"]["username"];
            $password = $_SESSION["webTvplayer"]["password"];
            $dataoffset = isset($_POST["dataoffset"]) ? $_POST["dataoffset"] : 0;
            $datalimit = isset($_POST["datalimit"]) ? $_POST["datalimit"] : 28;
            $bar = "/";
            if (substr($hostURL, -1) == "/") {
                $bar = "";
            }
            $ApiLink = $hostURL . $bar . "player_api.php?username=" . $username . "&password=" . $password . "&action=get_series";
            if ($CateGoryId != "all") {
                $ApiLink = $hostURL . $bar . "player_api.php?username=" . $username . "&password=" . $password . "&action=get_series&category_id=" . $CateGoryId;
            }
            $ApiData = webtvpanel_CallApiRequest($ApiLink);
            if ($ApiData["result"] == "success") {
                $SessionStroedUsername = $_SESSION["webTvplayer"]["username"];
                $SortingType = isset($_COOKIE[$SessionStroedUsername . "_series"]) && !empty($_COOKIE[$SessionStroedUsername . "_series"]) ? $_COOKIE[$SessionStroedUsername . "_series"] : "default";
                if ($SortingType != "default") {
                    $Arrayforsorting = array();
                    foreach ($ApiData["data"] as $StreamData) {
                        $BackdropArray = array();
                        if (!empty($StreamData->backdrop_path)) {
                            foreach ($StreamData->backdrop_path as $backVal) {
                                $BackdropArray[] = $backVal;
                            }
                        }
                        $Arrayforsorting[$SortingType == "topadded" ? $StreamData->last_modified : $StreamData->name] = (object) array("num" => $StreamData->num, "name" => $StreamData->name, "stream_type" => $StreamData->stream_type, "series_id" => $StreamData->series_id, "cover" => $StreamData->cover, "plot" => $StreamData->plot, "cast" => $StreamData->cast, "director" => $StreamData->director, "genre" => $StreamData->genre, "releaseDate" => $StreamData->releaseDate, "last_modified" => $StreamData->last_modified, "rating" => $StreamData->rating, "rating_5based" => $StreamData->rating_5based, "backdrop_path" => $BackdropArray, "youtube_trailer" => $StreamData->youtube_trailer, "episode_run_time" => $StreamData->episode_run_time, "category_id" => $StreamData->category_id, "rating" => $StreamData->rating);
                    }
                    array_multisort(array_keys($Arrayforsorting), SORT_NATURAL | SORT_FLAG_CASE, $Arrayforsorting);
                    if ($SortingType == "desc") {
                        $Arrayforsorting = array_reverse($Arrayforsorting, false);
                    }
                    $ApiData["data"] = $Arrayforsorting;
                }
                foreach ($ApiData["data"] as $SeriesData) {
                    $Icon = $SeriesData->cover;
                    if ($Icon == "") {
                        $Icon = "";
                    }
                    $counter++;
                    if ($dataoffset <= $counter && $counter <= $datalimit) {
                        $QuersyData = "moviename=" . $SeriesData->name . "&StreamId=" . $SeriesData->series_id . "&CateGoryId=" . $CateGoryId . "&posterImage=" . $Icon . "&plot=" . $SeriesData->plot . "&cast=" . $SeriesData->cast . "&genre=" . $SeriesData->genre . "&director=" . $SeriesData->director . "&rating=" . $SeriesData->rating . "&releaseDate=" . $SeriesData->releaseDate;
                        echo "\t\t\t\t<li class=\"thumb-b animate streamList rippler rippler-default sectionNo";
                        echo $SeriesData->series_id;
                        echo " un-";
                        echo $counter;
                        echo "\"  onclick=\"showInfo('";
                        echo $SeriesData->series_id;
                        echo "')\">\r\n\t\t\t\t<input type=\"hidden\" id=\"fullData-";
                        echo $SeriesData->series_id;
                        echo "\" value=\"";
                        echo webtvpanel_baseEncode($QuersyData);
                        echo "\">\r\n\t\t\t\t\t<input type=\"hidden\" class=\"serch_key\" value=\"";
                        echo $SeriesData->name;
                        echo "\" data-parentliclass=\"sectionNo";
                        echo $SeriesData->series_id;
                        echo "\">\r\n\t\t\t\t<div class=\"view view-tenth rippler rippler-default\"><img class=\"iconImage\" src=\"";
                        echo $Icon;
                        echo "\" onerror=\"this.src='images/no-poster.jpg';this.parentElement.className='view view-tenth showEI ';\">\r\n\t\t\t\t  <div class=\"mask\">\r\n\t\t\t\t    <h2>";
                        echo $SeriesData->name;
                        echo "</h2>\r\n\t\t\t\t    <!-- <div class=\"fav\"> <a href=\"#\"><span class=\"fa fa-heart-o fa-heart\"></span></a> <span>2016</span> </div> -->\r\n\t\t\t\t    <div class=\"n-ratting\">\r\n\t\t\t\t    \t";
                        $RatingIs = 0;
                        if (isset($SeriesData->rating_5based) && !empty($SeriesData->rating_5based)) {
                            $RatingIs = $SeriesData->rating_5based;
                        } else {
                            $RatingIs = $SeriesData->rating / 2;
                        }
                        echo webtvpanel_starRating($RatingIs);
                        echo "\t\t\t\t    </div>\r\n\t\t\t\t  </div>\r\n\t\t\t\t</div>\r\n\t\t\t\t</li>\r\n    \t\t";
                    }
                }
                if ($datalimit < $counter) {
                    echo "\t\t\t<center class=\"loading-loadBtn\">\r\n\t\t\t\t<button class=\"LoadMoreBtn btn btn-success rippler rippler-default\" data-dataoffset=\"";
                    echo $datalimit;
                    echo "\" data-categoryID=\"";
                    echo $_POST["categoryID"];
                    echo "\">Load More <i class=\"LoadingMoreFa fa fa-spin fa-spinner hideOnload\"></i></button>\r\n\t\t\t</center>\r\n\t\t\t";
                }
                exit;
            } else {
                echo "0";
                exit;
            }
        } else {
            if (isset($_POST["action"]) && $_POST["action"] == "getStreamsFromID") {
                $counter = 0;
                $hostURL = $_POST["hostURL"];
                $username = $_SESSION["webTvplayer"]["username"];
                $password = $_SESSION["webTvplayer"]["password"];
                $bar = "/";
                if (substr($hostURL, -1) == "/") {
                    $bar = "";
                }
                $ApiLink = $hostURL . $bar . "player_api.php?username=" . $username . "&password=" . $password . "&action=get_live_streams&category_id=" . $_POST["categoryID"];
                $StreamData = webtvpanel_CallApiRequest($ApiLink);
                if ($StreamData["result"] == "success") {
                    $SessionStroedUsername = $_SESSION["webTvplayer"]["username"];
                    $SortingType = isset($_COOKIE[$SessionStroedUsername . "_live"]) && !empty($_COOKIE[$SessionStroedUsername . "_live"]) ? $_COOKIE[$SessionStroedUsername . "_live"] : "default";
                    if ($SortingType != "default") {
                        $Arrayforsorting = array();
                        foreach ($StreamData["data"] as $StreamKeyIS) {
                            $BackdropArray = array();
                            if (!empty($StreamKeyIS->backdrop_path)) {
                                foreach ($StreamKeyIS->backdrop_path as $backVal) {
                                    $BackdropArray[] = $backVal;
                                }
                            }
                            $Arrayforsorting[$SortingType == "topadded" ? $StreamKeyIS->added : $StreamKeyIS->name] = (object) array("num" => $StreamKeyIS->num, "name" => $StreamKeyIS->name, "stream_type" => $StreamKeyIS->stream_type, "stream_id" => $StreamKeyIS->stream_id, "stream_icon" => $StreamKeyIS->stream_icon, "epg_channel_id" => $StreamKeyIS->epg_channel_id, "added" => $StreamKeyIS->added, "category_id" => $StreamKeyIS->category_id, "custom_sid" => $StreamKeyIS->custom_sid, "tv_archive" => $StreamKeyIS->tv_archive, "direct_source" => $StreamKeyIS->direct_source, "tv_archive_duration" => $StreamKeyIS->tv_archive_duration);
                        }
                        array_multisort(array_keys($Arrayforsorting), SORT_NATURAL | SORT_FLAG_CASE, $Arrayforsorting);
                        if ($SortingType == "desc") {
                            $Arrayforsorting = array_reverse($Arrayforsorting, false);
                        }
                        $StreamData["data"] = $Arrayforsorting;
                    }
                    foreach ($StreamData["data"] as $chanel) {
                        $chanelIcon = $chanel->stream_icon;
                        if ($chanelIcon == "") {
                            $chanelIcon = "images/no_logo.jpg";
                        }
                        $counter++;
                        echo "\t\t<li id=\"video";
                        echo $counter;
                        echo "\" class=\"streamList Playclick rippler rippler-inverse sectionNo";
                        echo $chanel->stream_id;
                        echo "\">\r\n\t\t\t<input type=\"hidden\" class=\"streamId\" data-streamtype=\"";
                        echo $chanel->stream_type;
                        echo "\" value=\"";
                        echo $chanel->stream_id;
                        echo "\">\r\n\t\t\t\t<span style=\"font-weight: bold;width: 50px;text-align: center;padding-top: 8px;\">";
                        echo $chanel->num;
                        echo "</span>\r\n                <span class=\"number\"><img src=\"";
                        echo $chanel->stream_icon;
                        echo "\" width=\"100%\" height=\"30px\" onerror=\"this.src='images/no_logo.jpg'\"></span>\r\n                <!-- <i class=\"fa fa-television\" aria-hidden=\"true\"></i> -->\r\n                <!-- <i class=\"fa fa-star hide\" aria-hidden=\"true\"></i> -->\r\n                \r\n                <input type=\"hidden\" class=\"serch_key\" value=\"";
                        echo $chanel->name;
                        echo "\" data-parentliclass=\"sectionNo";
                        echo $chanel->stream_id;
                        echo "\">\r\n                <label>";
                        echo $chanel->name;
                        echo "</label>\r\n                <i class=\"fa fa-repeat\" aria-hidden=\"true\" style=\"float: right;\"></i>\r\n              </li>\r\n\t\t\r\n\t\t";
                    }
                    exit;
                }
            }
            if (isset($_POST["action"]) && $_POST["action"] == "getRadioStreamsFromID") {
                $counter = 0;
                $hostURL = $_POST["hostURL"];
                $username = $_SESSION["webTvplayer"]["username"];
                $password = $_SESSION["webTvplayer"]["password"];
                $bar = "/";
                if (substr($hostURL, -1) == "/") {
                    $bar = "";
                }
                $ApiLink = $hostURL . $bar . "player_api.php?username=" . $username . "&password=" . $password . "&action=get_live_streams&category_id=" . $_POST["categoryID"];
                $StreamData = webtvpanel_CallApiRequest($ApiLink);
                if ($StreamData["result"] == "success") {
                    $SessionStroedUsername = $_SESSION["webTvplayer"]["username"];
                    $SortingType = isset($_COOKIE[$SessionStroedUsername . "_radio"]) && !empty($_COOKIE[$SessionStroedUsername . "_radio"]) ? $_COOKIE[$SessionStroedUsername . "_radio"] : "default";
                    if ($SortingType != "default") {
                        $Arrayforsorting = array();
                        foreach ($StreamData["data"] as $StreamKeyIS) {
                            if ($StreamKeyIS->stream_type == "radio_streams") {
                                $Arrayforsorting[$SortingType == "topadded" ? $StreamKeyIS->added : $StreamKeyIS->name] = (object) array("num" => $StreamKeyIS->num, "name" => $StreamKeyIS->name, "stream_type" => $StreamKeyIS->stream_type, "stream_id" => $StreamKeyIS->stream_id, "stream_icon" => $StreamKeyIS->stream_icon, "epg_channel_id" => $StreamKeyIS->epg_channel_id, "added" => $StreamKeyIS->added, "category_id" => $StreamKeyIS->category_id, "custom_sid" => $StreamKeyIS->custom_sid, "tv_archive" => $StreamKeyIS->tv_archive, "direct_source" => $StreamKeyIS->direct_source, "tv_archive_duration" => $StreamKeyIS->tv_archive_duration);
                            }
                        }
                        array_multisort(array_keys($Arrayforsorting), SORT_NATURAL | SORT_FLAG_CASE, $Arrayforsorting);
                        if ($SortingType == "desc") {
                            $Arrayforsorting = array_reverse($Arrayforsorting, false);
                        }
                        $StreamData["data"] = $Arrayforsorting;
                    } else {
                        $Arrayforsorting = array();
                        foreach ($StreamData["data"] as $StreamKeyIS) {
                            if ($StreamKeyIS->stream_type == "radio_streams") {
                                $Arrayforsorting[] = (object) array("num" => $StreamKeyIS->num, "name" => $StreamKeyIS->name, "stream_type" => $StreamKeyIS->stream_type, "stream_id" => $StreamKeyIS->stream_id, "stream_icon" => $StreamKeyIS->stream_icon, "epg_channel_id" => $StreamKeyIS->epg_channel_id, "added" => $StreamKeyIS->added, "category_id" => $StreamKeyIS->category_id, "custom_sid" => $StreamKeyIS->custom_sid, "tv_archive" => $StreamKeyIS->tv_archive, "direct_source" => $StreamKeyIS->direct_source, "tv_archive_duration" => $StreamKeyIS->tv_archive_duration);
                            }
                        }
                        $StreamData["data"] = $Arrayforsorting;
                    }
                    foreach ($StreamData["data"] as $chanel) {
                        $chanelIcon = $chanel->stream_icon;
                        if ($chanelIcon == "") {
                            $chanelIcon = "images/no_logo.jpg";
                        }
                        $counter++;
                        echo "\t\t<li id=\"video";
                        echo $counter;
                        echo "\" class=\"streamList Playclick rippler rippler-inverse sectionNo";
                        echo $chanel->stream_id;
                        echo "\">\r\n\t\t\t<input type=\"hidden\" class=\"streamId\" data-streamtype=\"";
                        echo $chanel->stream_type;
                        echo "\" value=\"";
                        echo $chanel->stream_id;
                        echo "\">\r\n\t\t\t<span style=\"font-weight: bold;width: 50px;text-align: center;padding-top: 8px;\">";
                        echo $chanel->num;
                        echo "</span>\r\n                <span class=\"number\"><img src=\"";
                        echo $chanel->stream_icon;
                        echo "\" width=\"100%\" height=\"30px\" onerror=\"this.src='images/no_logo.jpg'\"></span>\r\n                <!-- <i class=\"fa fa-television\" aria-hidden=\"true\"></i> -->\r\n                <!-- <i class=\"fa fa-star hide\" aria-hidden=\"true\"></i> -->\r\n                \r\n                <input type=\"hidden\" class=\"serch_key\" value=\"";
                        echo $chanel->name;
                        echo "\" data-parentliclass=\"sectionNo";
                        echo $chanel->stream_id;
                        echo "\">\r\n                <label>";
                        echo $chanel->name;
                        echo "</label>\r\n                <i class=\"fa fa-repeat\" aria-hidden=\"true\" style=\"float: right;\"></i>\r\n              </li>\r\n\t\t\r\n\t\t";
                    }
                    exit;
                }
            }
            if (isset($_POST["action"]) && $_POST["action"] == "getCaptchStreamsFromID") {
                $counter = 0;
                $hostURL = $_POST["hostURL"];
                $username = $_SESSION["webTvplayer"]["username"];
                $password = $_SESSION["webTvplayer"]["password"];
                $bar = "/";
                if (substr($hostURL, -1) == "/") {
                    $bar = "";
                }
                $ApiLink = $hostURL . $bar . "player_api.php?username=" . $username . "&password=" . $password . "&action=get_live_streams&category_id=" . $_POST["categoryID"];
                $StreamData = webtvpanel_CallApiRequest($ApiLink);
                $FinalTotalConditional = 0;
                if ($StreamData["result"] == "success") {
                    $SessionStroedUsername = $_SESSION["webTvplayer"]["username"];
                    $SortingType = isset($_COOKIE[$SessionStroedUsername . "_catchup"]) && !empty($_COOKIE[$SessionStroedUsername . "_catchup"]) ? $_COOKIE[$SessionStroedUsername . "_catchup"] : "default";
                    if ($SortingType != "default") {
                        $Arrayforsorting = array();
                        foreach ($StreamData["data"] as $StreamKeyIS) {
                            $BackdropArray = array();
                            if (!empty($StreamKeyIS->backdrop_path)) {
                                foreach ($StreamKeyIS->backdrop_path as $backVal) {
                                    $BackdropArray[] = $backVal;
                                }
                            }
                            $Arrayforsorting[$SortingType == "topadded" ? $StreamKeyIS->added : $StreamKeyIS->name] = (object) array("num" => $StreamKeyIS->num, "name" => $StreamKeyIS->name, "stream_type" => $StreamKeyIS->stream_type, "stream_id" => $StreamKeyIS->stream_id, "stream_icon" => $StreamKeyIS->stream_icon, "epg_channel_id" => $StreamKeyIS->epg_channel_id, "added" => $StreamKeyIS->added, "category_id" => $StreamKeyIS->category_id, "custom_sid" => $StreamKeyIS->custom_sid, "tv_archive" => $StreamKeyIS->tv_archive, "direct_source" => $StreamKeyIS->direct_source, "tv_archive_duration" => $StreamKeyIS->tv_archive_duration);
                        }
                        array_multisort(array_keys($Arrayforsorting), SORT_NATURAL | SORT_FLAG_CASE, $Arrayforsorting);
                        if ($SortingType == "desc") {
                            $Arrayforsorting = array_reverse($Arrayforsorting, false);
                        }
                        $StreamData["data"] = $Arrayforsorting;
                    }
                    foreach ($StreamData["data"] as $chanel) {
                        if ($chanel->tv_archive == 1) {
                            $chanelIcon = $chanel->stream_icon;
                            if ($chanelIcon == "") {
                                $chanelIcon = "images/no_logo.jpg";
                            }
                            $counter++;
                            echo "\t\t\t\t<li id=\"video";
                            echo $counter;
                            echo "\" class=\"streamList Playclick rippler rippler-default sectionNo";
                            echo $chanel->stream_id;
                            echo "\">\r\n\t\t\t\t<input type=\"hidden\" class=\"streamId\" data-streamtype=\"";
                            echo $chanel->stream_type;
                            echo "\" value=\"";
                            echo $chanel->stream_id;
                            echo "\">\r\n\t\t\t\t<span style=\"font-weight: bold;width: 50px;text-align: center;padding-top: 8px;\">";
                            echo $chanel->num;
                            echo "</span>\r\n\t\t\t\t<span class=\"number\"><img src=\"";
                            echo $chanel->stream_icon;
                            echo "\" width=\"100%\" height=\"30px\" onerror=\"this.src='images/no_logo.jpg'\"></span>\r\n\t\t\t\t<!-- <i class=\"fa fa-television\" aria-hidden=\"true\"></i> -->\r\n\t\t\t\t<!-- <i class=\"fa fa-star hide\" aria-hidden=\"true\"></i> -->\r\n\t\t\t\t<input type=\"hidden\" class=\"serch_key\" value=\"";
                            echo $chanel->name;
                            echo "\" data-parentliclass=\"sectionNo";
                            echo $chanel->stream_id;
                            echo "\">\r\n\t\t\t\t<label>";
                            echo $chanel->name;
                            echo "</label>\r\n\t\t\t\t<i class=\"fa fa-repeat\" aria-hidden=\"true\" style=\"float: right;\"></i>\r\n\t\t\t\t</li>\r\n\t\t\t\t";
                            $FinalTotalConditional++;
                        }
                    }
                    if ($FinalTotalConditional == 0) {
                        echo "0";
                    }
                    exit;
                }
            }
            if (isset($_POST["action"]) && $_POST["action"] == "GetCaptchaByStreamid") {
                $GlobalTimeFormat = "12";
                if (isset($_COOKIE["settings_array"])) {
                    $SettingArray = json_decode($_COOKIE["settings_array"]);
                    $SessionStroedUsername = $_SESSION["webTvplayer"]["username"];
                    if (isset($SettingArray->{$SessionStroedUsername}) && !empty($SettingArray->{$SessionStroedUsername})) {
                        $GlobalTimeFormat = $SettingArray->{$SessionStroedUsername}->timeformat;
                    }
                }
                $Formatis = "h:i A";
                if ($GlobalTimeFormat == "24") {
                    $Formatis = "H:i";
                }
                $CurrentTime = $_POST["CurrentTime"];
                $StreamId = $_POST["StreamId"];
                $username = $_SESSION["webTvplayer"]["username"];
                $password = $_SESSION["webTvplayer"]["password"];
                $hostURL = $_POST["hostURL"];
                $ApiLink = $hostURL . "player_api.php?username=" . $username . "&password=" . $password . "&action=get_simple_data_table&stream_id=" . $StreamId;
                $RequestForEpg = webtvpanel_CallApiRequest($ApiLink);
                if (!empty($RequestForEpg) && $RequestForEpg["result"] == "success") {
                    $CurrentDate = date("Y:m:d", $CurrentTime);
                    if (!empty($RequestForEpg["data"]->epg_listings)) {
                        $OnlyDates = array();
                        foreach ($RequestForEpg["data"]->epg_listings as $ResVal) {
                            if ($ResVal->has_archive == 1) {
                                $OnlyDateVar = date("Y:m:d", strtotime($ResVal->start));
                                $ValDate = date("d/m/Y", strtotime($ResVal->start));
                                if ($OnlyDateVar <= $CurrentDate) {
                                    $OnlyDates[$OnlyDateVar] = $ValDate;
                                }
                            }
                        }
                        if (!empty($OnlyDates)) {
                            echo "\t    \t<div class=\"panel-heading\">\t\r\n\t    \t\t<ul class=\"nav nav-tabs\">\r\n\t\t    \t";
                            $OnlyDates = array_reverse($OnlyDates);
                            $TotalDates = count($OnlyDates);
                            $Counter = 1;
                            foreach ($OnlyDates as $OnlyDate => $Val) {
                                if ($Counter <= 4) {
                                    echo "  \r\n\t                    <li class=\"";
                                    echo $Counter == 1 ? "active" : "";
                                    echo " rippler rippler-default\">\r\n\t                    \t<a href=\"#TabNo";
                                    echo $Counter;
                                    echo "\" data-toggle=\"tab\">\r\n\t                    \t\t";
                                    echo $Val;
                                    echo "                    \t\t\t\r\n\t                    \t</a>\r\n\t                    </li>\r\n\t\t    \t\t";
                                }
                                $Counter++;
                            }
                            if (4 < $TotalDates) {
                                echo "\t\t    \t\t<li class=\"dropdown\">\r\n\t                    <a href=\"#\" data-toggle=\"dropdown\">More <span class=\"caret rippler rippler-default\"></span></a>\r\n\t                    <ul class=\"dropdown-menu\" role=\"menu\">\r\n\t                        ";
                                $Counter1 = 1;
                                foreach ($OnlyDates as $OnlyDate => $Val) {
                                    if (4 < $Counter1) {
                                        echo "\t                \t\t\t\t\t<li><a href=\"#TabNo";
                                        echo $Counter1;
                                        echo "\" data-toggle=\"tab\">";
                                        echo $Val;
                                        echo "</a></li>\t\r\n\t                \t\t\t\t\t";
                                    }
                                    $Counter1++;
                                }
                                echo "\t                    </ul>\r\n\t                </li>\t\r\n\t\t    \t\t";
                            }
                            echo "\t    \t\t</ul>\t\r\n\t    \t</div>\r\n\t    \t<div class=\"panel-body\">\r\n\t            <div class=\"tab-content\">\r\n\t            \t\t";
                            $TabCounter = 1;
                            $CaptchaCounter = 1;
                            foreach ($OnlyDates as $OnlyDate => $Val) {
                                echo "\t                    \t\t<div class=\"tab-pane fade customTab ";
                                echo $TabCounter == 1 ? "in active" : "";
                                echo "\" id=\"TabNo";
                                echo $TabCounter;
                                echo "\" >\r\n\t                    \t\t\t";
                                foreach ($RequestForEpg["data"]->epg_listings as $ResVal) {
                                    if ($ResVal->has_archive == 1) {
                                        $OnlyDateVal = date("Y:m:d", strtotime($ResVal->start));
                                        if ($OnlyDateVal == $OnlyDate) {
                                            $ACtiveClass = "";
                                            $NowPLaying = "";
                                            $StartTimming = strtotime($ResVal->start);
                                            $EndTimming = strtotime($ResVal->end);
                                            $interval = abs($EndTimming - $StartTimming);
                                            $minutes = round($interval / 60);
                                            echo "\t\t\t    \t\t\t\t\t\t\t\t\t<div class=\"epginfo catchupclick ";
                                            echo $ACtiveClass;
                                            echo " cp-";
                                            echo $CaptchaCounter;
                                            echo "\" data-timediff=\"";
                                            echo $minutes;
                                            echo "\" data-starttime=\"";
                                            echo date("Y-m-d:h-i", $StartTimming);
                                            echo "\" data-streamid=\"";
                                            echo $StreamId;
                                            echo "\">\r\n\t\t\t    \t\t\t\t\t\t\t\t\t\t";
                                            echo date($Formatis, $StartTimming);
                                            echo "\t\t\t    \t\t\t\t\t\t\t\t\t\t-\r\n\t\t\t    \t\t\t\t\t\t\t\t\t\t";
                                            echo date($Formatis, $EndTimming);
                                            echo "\t\t\t    \t\t\t\t\t\t\t\t\t\t&nbsp; \r\n\t\t\t    \t\t\t\t\t\t\t\t\t\t";
                                            echo base64_decode($ResVal->title);
                                            echo " \r\n\t\t\t    \t\t\t\t\t\t\t\t\t\t&nbsp;\r\n\t\t\t    \t\t\t\t\t\t\t\t\t\t";
                                            echo $NowPLaying;
                                            echo "\t\r\n\t\t\t    \t\t\t\t\t\t\t\t\t</div>\t\r\n\t\t\t    \t\t\t\t\t\t\t\t\t";
                                            $CaptchaCounter++;
                                        }
                                    }
                                }
                                echo "\t                    \t\t</div>\r\n\t                    \t";
                                $TabCounter++;
                            }
                            echo "\t\r\n\t            </div>\r\n\t        </div>\t\r\n\t    \t";
                            exit;
                        } else {
                            echo "";
                            exit;
                        }
                    } else {
                        echo "";
                        exit;
                    }
                } else {
                    echo "";
                    exit;
                }
            } else {
                if (isset($_POST["action"]) && $_POST["action"] == "getLiveVideoLink") {
                    $hostURL = $_POST["hostURL"];
                    $streamID = $_POST["streamID"];
                    $streamType = $_POST["streamType"];
                    $username = $_SESSION["webTvplayer"]["username"];
                    $password = $_SESSION["webTvplayer"]["password"];
                    $bar = "/";
                    if (substr($hostURL, -1) == "/") {
                        $bar = "";
                    }
                    $videoLink = $hostURL . $bar . "live/" . $username . "/" . $password . "/" . $streamID . ".m3u8";
                    echo $videoLink;
                    exit;
                }
                if (isset($_POST["action"]) && $_POST["action"] == "getSeriesInfo") {
                    $SeriesId = $_POST["seriesID"];
                    $username = $_SESSION["webTvplayer"]["username"];
                    $password = $_SESSION["webTvplayer"]["password"];
                    $hostURL = $_POST["hostURL"];
                    $fullDataVal = $_POST["fullDataVal"];
                    $DataSting = parse_str(webtvpanel_baseDecode($fullDataVal));
                    $StreamIDIS = isset($StreamId) && !empty($StreamId) ? $StreamId : "n/A";
                    $MainStreamName = isset($moviename) && !empty($moviename) ? $moviename : "n/A";
                    $MainStreamCategoryID = isset($CateGoryId) && !empty($CateGoryId) ? $CateGoryId : "n/A";
                    $MainStreamCover = isset($posterImage) && !empty($posterImage) ? $posterImage : "images/no-poster.jpg";
                    $MainStreamPlot = isset($plot) && !empty($plot) ? $plot : "n/A";
                    $MainStreamCast = isset($cast) && !empty($cast) ? $cast : "n/A";
                    $MainStreamGenre = isset($genre) && !empty($genre) ? $genre : "n/A";
                    $MainStreamDirector = isset($director) && !empty($director) ? $director : "n/A";
                    $MainStreamRating = isset($rating) && !empty($rating) ? $rating : "0";
                    $MainStreamReleaseDate = isset($releaseDate) && !empty($releaseDate) ? $releaseDate : "n/A";
                    $ApiLink = $hostURL . "player_api.php?username=" . $username . "&password=" . $password . "&action=get_series_info&series_id=" . $SeriesId;
                    $SeriesData = webtvpanel_CallApiRequest($ApiLink);
                    if ($SeriesData["result"] == "success") {
                        if (isset($SeriesData["data"]->episodes) && !empty($SeriesData["data"]->episodes)) {
                            $AllSeasonData = isset($SeriesData["data"]->seasons) && !empty($SeriesData["data"]->seasons) ? $SeriesData["data"]->seasons : "";
                            $SeasonCoverImage = array();
                            if ($AllSeasonData != "") {
                                foreach ($AllSeasonData as $SeasonDataKey) {
                                    $SeasonCoverImage[$SeasonDataKey->season_number] = $SeasonDataKey->cover;
                                }
                            }
                            $OnloadAvtiveEpisode = 0;
                            $SeasonsIdData = array();
                            $Appepisodes = array();
                            $MainposterImage = $MainStreamCover;
                            $MainMovieName = $MainStreamName;
                            $MainMovieDesc = $MainStreamPlot;
                            $MainMoviegenre = $MainStreamGenre;
                            $MainMoviereleaseDate = $MainStreamReleaseDate;
                            $MainMovierrating_5based = $MainStreamRating != 0 ? $MainStreamRating / 2 : "0";
                            $MainMovierdirector = $MainStreamDirector;
                            $MainMoviercast = $MainStreamCast;
                            if (!empty($SeriesData["data"]->info)) {
                                $SeriesDetails = $SeriesData["data"]->info;
                                if ($SeriesDetails->cover != "") {
                                    $MainposterImage = $SeriesDetails->cover;
                                }
                                $MainMovieName = $SeriesDetails->name != "" ? $SeriesDetails->name : "n/A";
                                $MainMovieDesc = $SeriesDetails->plot != "" ? $SeriesDetails->plot : "n/A";
                                $MainMoviegenre = $SeriesDetails->genre != "" ? $SeriesDetails->genre : "n/A";
                                $MainMoviereleaseDate = $SeriesDetails->releaseDate != "" ? $SeriesDetails->releaseDate : "n/A";
                                $MainMovierrating_5based = $SeriesDetails->rating_5based != "" ? $SeriesDetails->rating_5based : "n/A";
                                $MainMovierdirector = $SeriesDetails->director != "" ? $SeriesDetails->director : "n/A";
                                $MainMoviercast = $SeriesDetails->cast != "" ? $SeriesDetails->cast : "n/A";
                            }
                            if (!empty($SeriesData["data"]->episodes)) {
                                $Appepisodes = $SeriesData["data"]->episodes;
                                foreach ($SeriesData["data"]->episodes as $episodes) {
                                    foreach ($episodes as $episodesData) {
                                        $SeasonsIdData[$episodesData->season] = "season";
                                    }
                                }
                            }
                            echo "\t\t     <div class=\"modal-content\">\r\n\t\t     <div class=\"player_changeIssue alert alert-info\" style=\"position: fixed; top: -300px;left: 35%;\">\r\n\t      \t\tUnable to play this format in Jw player trying with aj player.\r\n\t        </div>\r\n\t\t      <div class=\"modal-header\" style=\"border:0;\"> <span class=\"p-close rippler rippler-default\" data-dismiss=\"modal\" aria-hidden=\"true\">x</span> </div>\r\n\t\t      <div class=\"modal-body\">\r\n\t\t        <div class=\"popup-content t-s\">\r\n\t\t          <div class=\"pull-left\" style=\"width: 10%;\">\r\n\t\t            <div class=\"poster\">\r\n\t\t              <div class=\"poster-img\"><img src=\"";
                            echo $MainposterImage;
                            echo "\" alt=\"\" onerror=\"this.src='images/no-poster.jpg';\" class=\"img-responsive\"></div>\r\n\t\t            </div>\r\n\t\t            <div class=\"ts-content\">\r\n\t\t            <div class=\"column seasons\">\r\n\t\t            \t";
                            if (!empty($SeasonsIdData)) {
                                echo "\t\t            \t\t<ul>\r\n\t\t\t            \t\t";
                                $ConditionCounter = 1;
                                foreach ($SeasonsIdData as $SeasonNumber => $val) {
                                    echo "\t\t\t            \t\t\t<li class=\"";
                                    echo $ConditionCounter == 1 ? "active" : "";
                                    echo "  rippler rippler-default\"><a data-toggle=\"tab\" href=\"#s-";
                                    echo $SeasonNumber;
                                    echo "\">Season ";
                                    echo $SeasonNumber;
                                    echo "</a></li>\r\n\t\t\t            \t\t\t";
                                    $ConditionCounter++;
                                }
                                echo "\t\t            \t\t</ul>\t\r\n\t\t            \t\t";
                            }
                            echo "\t\r\n\t\t            </div>\r\n\t\t        </div>\r\n\t\t          </div>\r\n\t\t          <div class=\"col-sm-9 col-md-10 col-xs-12\">\r\n\t\t            <div class=\"poster-details1\">\r\n\t\t              <h2>";
                            echo $MainMovieName;
                            echo "</h2>\r\n\t\t              <ul>\r\n\t\t                <li><i class=\"fa fa-anchor\"></i>";
                            echo $MainMoviegenre;
                            echo "</li>\r\n\t\t                <li><i class=\"fa fa-calendar\"></i>";
                            echo $MainMoviereleaseDate;
                            echo "</li>\r\n\t\t                <li> ";
                            echo webtvpanel_starRating($MainMovierrating_5based);
                            echo "</li>\r\n\t\t              </ul>\r\n\t\t              \r\n\t\t            </div>\r\n\t\t          </div>\r\n\t\t          <!-- <div class=\"clearfix\"></div> -->\r\n\t\t          <div class=\"ts-content\">\r\n\t\t            \r\n\t\t            <div class=\"column episodes\">\r\n\t\t              <div class=\"tab-content\">\r\n\t\t              ";
                            if (!empty($SeasonsIdData)) {
                                $ConditionCounter2 = 1;
                                foreach ($SeasonsIdData as $SeasonNumber => $val) {
                                    echo "\t\t\t                   <ul id=\"s-";
                                    echo $SeasonNumber;
                                    echo "\" class=\"tab-pane fade in ";
                                    echo $ConditionCounter2 == 1 ? "active" : "";
                                    echo "\">\r\n\t\t\t                    ";
                                    $CounterCon2 = 1;
                                    foreach ($Appepisodes as $episodes) {
                                        foreach ($episodes as $episodesData) {
                                            if ($episodesData->season == $SeasonNumber) {
                                                if ($CounterCon2 == 1) {
                                                    $OnloadAvtiveEpisode = $episodesData->id;
                                                }
                                                echo "\t          \t\t\t\t\t\t\t\t<li class=\"";
                                                echo $CounterCon2 == 1 ? "active" : "";
                                                echo " rippler rippler-default\"><a data-episid=\"";
                                                echo $episodesData->id;
                                                echo "\"  data-toggle=\"tab\" href=\"#epis-";
                                                echo $episodesData->id;
                                                echo "\"><b>";
                                                echo $CounterCon2;
                                                echo " </b>";
                                                echo urldecode($episodesData->title);
                                                echo "</a></li>\r\n\t          \t\t\t\t\t\t\t\t";
                                                $CounterCon2++;
                                            }
                                        }
                                    }
                                    echo "\t\t\t                   </ul>\r\n\t\t                    ";
                                    $ConditionCounter2++;
                                }
                            }
                            echo "\t\t              </div>\r\n\t\t            </div>\r\n\t\t            <div class=\"column w-content\">\r\n\t\t              <div class=\"tab-content\">\r\n\t\t              \t<div class=\"PlayerHolder\" data-ajplayer=\"\" data-flowplayer=\"\" data-jwplayer=\"\">\r\n      \t\t\t\t\t\t\t\t<div id=\"player-holder\" class=\"hideOnLoad\"  style=\"border:solid 2px #fff; height: auto !important;\"  >\r\n      \t\t\t\t\t\t\t\t</div>\r\n      \t\t\t\t\t\t\t\t</div>\r\n\t\t              \t\t";
                            $CounterCon3 = 1;
                            foreach ($Appepisodes as $episodes) {
                                foreach ($episodes as $episodesData) {
                                    echo "      \t\t\t\t\t\t\t\t\r\n      \t\t\t\t\t\t\t\t<button id=\"backToInfo-";
                                    echo $episodesData->id;
                                    echo "\" data-episid=\"";
                                    echo $episodesData->id;
                                    echo "\" class=\"backToInfo btn btn-info hideOnLoad rippler rippler-default\">Back to Info</button>\r\n  \t\t\t\t\t\t\t\t\t<div id=\"epis-";
                                    echo $episodesData->id;
                                    echo "\" class=\"tab-pane fade in ";
                                    echo $CounterCon3 == 1 ? "active" : "";
                                    echo "\">\r\n  \t\t\t\t\t\t\t\t\t\t<h2>";
                                    echo urldecode($episodesData->title);
                                    echo "</h2>\r\n\t\t\t\t\t\t                <h5>Episode ";
                                    echo $episodesData->episode_num;
                                    echo "</h5>\r\n\t\t\t\t\t\t                <div class=\"row\">\r\n\t  \t\t\t\t\t\t\t\t\t  <div class=\"col-md-3 seasonIfb";
                                    echo $episodesData->season;
                                    echo "\">\r\n\t  \t\t\t\t\t\t\t\t\t  \t\t";
                                    $EpisodesCover = $MainposterImage;
                                    if (!empty($SeasonCoverImage[$episodesData->season])) {
                                        $EpisodesCover = $SeasonCoverImage[$episodesData->season];
                                    }
                                    echo "\t\t\t\t\t\t\t                  <img src=\"";
                                    echo $EpisodesCover;
                                    echo "\" alt=\"\" onerror=\"this.src='images/no-poster.jpg';\" class=\"img-responsive\">\r\n\t\t\t\t\t\t                  </div>\r\n\t  \t\t\t\t\t\t\t\t\t  <div class=\"col-md-9\">\r\n\t\t\t\t\t\t\t                  <p cal>";
                                    echo $episodesData->info->plot != "" ? $episodesData->info->plot : "n/A";
                                    echo "</p>\r\n\t\t\t\t\t\t                  </div>\r\n\t\t\t\t\t\t                </div>\r\n\t\t\t\t\t                  <div class=\"watch-now row rippler rippler-default\">\r\n\t\t\t\t\t                    <button onclick=\"watchnow('";
                                    echo $episodesData->id;
                                    echo "','";
                                    echo $episodesData->container_extension;
                                    echo "')\">watch it now</button>\r\n\t\t\t\t\t                  </div>\r\n\t\t\t\t\t                </div>\r\n      \t\t\t\t\t\t\t\t";
                                    $CounterCon3++;
                                }
                            }
                            echo "\t\t              \t\t\r\n\t\t              </div>\r\n\t\t              <!--tab-content--> \r\n\t\t            </div>\r\n\t\t            <div class=\"clearfix\"></div>\r\n\t\t            <h3 class=\"descHeading\">Description</h3>\r\n\t\t            <p class=\"decription\">";
                            echo $MainMovieDesc;
                            echo "</p>\r\n\t\t          </div>\r\n\t\t        </div>\r\n\t\t      </div>\r\n\t\t    </div>\t\r\n\t\t     ";
                            exit;
                        } else {
                            echo "0";
                            exit;
                        }
                    } else {
                        echo "0";
                        exit;
                    }
                } else {
                    if (isset($_POST["action"]) && $_POST["action"] == "getMovieInfo") {
                        $username = $_SESSION["webTvplayer"]["username"];
                        $password = $_SESSION["webTvplayer"]["password"];
                        $hostURL = $_POST["hostURL"];
                        $movieID = $_POST["movieID"];
                        $fullDataVal = $_POST["fullDataVal"];
                        $DataSting = parse_str(webtvpanel_baseDecode($fullDataVal));
                        $StreamIDIS = isset($StreamId) && !empty($StreamId) ? $StreamId : "n/A";
                        $MainStreamName = isset($moviename) && !empty($moviename) ? $moviename : "n/A";
                        $Mainextension = isset($extension) && !empty($extension) ? $extension : "n/A";
                        $rating5 = isset($rating5) && !empty($rating5) ? $rating5 : "";
                        $ApiLink = $hostURL . "player_api.php?username=" . $username . "&password=" . $password . "&action=get_vod_info&vod_id=" . $movieID;
                        $movieData = webtvpanel_CallApiRequest($ApiLink);
                        if ($movieData["result"] == "success") {
                            if (isset($movieData["data"]->movie_data)) {
                                $MainStreamName = $movieData["data"]->movie_data->name != "" ? $movieData["data"]->movie_data->name : "n/A";
                                $Mainextension = $movieData["data"]->movie_data->container_extension != "" ? $movieData["data"]->movie_data->container_extension : "n/A";
                            }
                            echo "\t\t<div class=\"modal-content\">\r\n\t\t<div class=\"player_changeIssue alert alert-info\" style=\"position: fixed; top: -300px;left: 35%;\">\r\n      \t\tUnable to play this format in Jw player trying with aj player.\r\n        </div>\r\n      <div class=\"modal-header\" style=\"border:0;\"> <span class=\"p-close rippler rippler-default\" data-dismiss=\"modal\" aria-hidden=\"true\">x</span> </div>\r\n      <div class=\"modal-body\">\r\n        <div class=\"popup-content\">\r\n          <div class=\"col-sm-5 col-lg-2 col-md-2 col-xs-7\">\r\n            <div class=\"poster\">\r\n              <div class=\"poster-img\"><img src=\"";
                            echo $movieData["data"]->info->movie_image;
                            echo "\" onerror=\"this.src='images/no-poster.jpg';\" alt=\"\" class=\"img-responsive\"></div>\r\n              <div class=\"ratting-bar row\">\r\n                <div class=\"stars \" style=\"text-align: center;\"> ";
                            $rate5 = $rating5;
                            if ($rating5 == "") {
                                $rate10 = explode("/", $movieData["data"]->info->rating);
                                $rate5 = intval($rate10[0]) / 5;
                            }
                            if (strpos($rate5, ".") !== false) {
                                $rate5 = floatval($rate5);
                            } else {
                                $rate5 = intval($rate5);
                            }
                            echo webtvpanel_starRating($rate5);
                            echo "</div>\r\n              </div>\r\n            </div>\r\n            <button class=\"backToInfo btn btn-info hideOnLoad rippler rippler-inverse\">Back To Info</button>\r\n          </div>\r\n          <div class=\"col-sm-7 col-lg-9 col-md-8 col-xs-12\">\r\n          \t<div class=\"PlayerHolder\" data-ajplayer=\"\" data-flowplayer=\"\" data-jwplayer=\"\">\r\n          \t<div id=\"player-holder\" class=\"hideOnLoad\"></div>\r\n          </div>\r\n            <div class=\"poster-details\">\r\n              <h2>";
                            echo $MainStreamName;
                            echo "</h2>\r\n              <ul class=\"col-md-6 col-sm-12\">\r\n                <li class=\"i-year\">";
                            echo $movieData["data"]->info->releasedate != "" ? $movieData["data"]->info->releasedate : "n/A";
                            echo "</li>\r\n                <li class=\"i-duration\">";
                            echo $movieData["data"]->info->duration_secs != "" ? round(intval($movieData["data"]->info->duration_secs) / 60) . " Minutes" : "n/A";
                            echo " </li>\r\n                <li class=\"i-movie\">";
                            echo $movieData["data"]->info->genre != "" ? $movieData["data"]->info->genre : "n/A";
                            echo "</li>\r\n                <!-- <li class=\"i-trailer\"><a href=\"#\">";
                            echo $movieData["data"]->info->releasedate;
                            echo "</a></li> -->\r\n              </ul>\r\n\r\n              <ul class=\"col-md-6 col-sm-12\">\r\n                <li class=\"i-cast\"><p>";
                            if (40 <= strlen($movieData["data"]->info->cast)) {
                                echo $movieData["data"]->info->cast . "</p><button class=\"showCast btn btn-sm btn-info rippler rippler-default\">Show Cast</button>";
                            } else {
                                echo $movieData["data"]->info->cast ? $movieData["data"]->info->cast : "n/A";
                            }
                            echo "</li>\r\n                <li class=\"i-director\">";
                            echo $movieData["data"]->info->director != "" ? $movieData["data"]->info->director : "n/A";
                            echo "</li>\r\n                \r\n                <!-- <li class=\"i-trailer\"><a href=\"#\">";
                            echo $movieData["data"]->info->releasedate;
                            echo "</a></li> -->\r\n              </ul>\r\n              <p class=\"pull-left\">\r\n              \t";
                            $ShowDescription = "n/A";
                            if (isset($movieData["data"]->info->description) && $movieData["data"]->info->description != "") {
                                $ShowDescription = $movieData["data"]->info->description;
                            } else {
                                if (isset($movieData["data"]->info->plot) && $movieData["data"]->info->plot != "") {
                                    $ShowDescription = $movieData["data"]->info->plot;
                                }
                            }
                            echo $ShowDescription;
                            echo "          \t\t\r\n              </p>\r\n              <div class=\"fav row\">\r\n                <div class=\"res-list hide\">\r\n                  <select>\r\n                    <option>480p</option>\r\n                    <option selected>720p</option>\r\n                    <option>1080p</option>\r\n                  </select>\r\n                </div>\r\n                <button class=\"gd hide\">3D</button>\r\n                <button class=\"add-fav hide\"></button>\r\n              </div>\r\n              \r\n              <div class=\"watch-now row\">\r\n                <button data-streamID=\"";
                            echo $StreamIDIS;
                            echo "\" data-ext=\"";
                            echo $Mainextension;
                            echo "\" onclick=\"watchMovie('";
                            echo $StreamIDIS;
                            echo ".";
                            echo $Mainextension;
                            echo "')\" class=\" rippler rippler-default\">watch it now</button>\r\n              </div>\r\n            </div>\r\n          </div>\r\n        </div>\r\n      </div>\r\n    </div>\r\n\t\t";
                        }
                    }
                }
            }
        }
    }
} else {
    echo "Please verify that function.php file exists";
    exit;
}

?>
