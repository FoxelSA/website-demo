<!DOCTYPE html>
<html lang="en">
<?php

/**
 * Demo Website of FOXEL SA, Geneva Switzerland.
 *
 * Copyright (c) 2014 FOXEL SA - http://foxel.ch
 * Please read <http://foxel.ch/license> for more information.
 *
 *
 * Author(s):
 *
 *      Alexandre Kraft <a.kraft@foxel.ch>
 *
 *
 * This file is part of the FOXEL project <http://foxel.ch>.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * Additional Terms:
 *
 *      You are required to preserve legal notices and author attributions in
 *      that material or in the Appropriate Legal Notices displayed by works
 *      containing it.
 *
 *      You are required to attribute the work as explained in the "Usage and
 *      Attribution" section of <http://foxel.ch/license>.
 */


/**
 * _sortViews()
 */
function _sortViews($a,$b) {
    if ($a->order == $b->order)
        return ($a->pid < $b->pid) ? -1 : 1;
    return ($a->order < $b->order) ? -1 : 1;
}

// request
$s = isset($_GET['s']) ? trim(strtolower($_GET['s'])) : NULL;
$p = isset($_GET['p']) ? (int)trim($_GET['p']) : NULL;

// read json
$json = json_decode(file_get_contents('config.json'));
$sets = &$json->config->sets;

// read auth json
if (file_exists('auth.json')) {
    $auth = json_decode(file_get_contents('auth.json'));
    $authsets = &$auth->config->sets;
    foreach ($authsets as &$authset)
        $authset->auth = true;
    $sets = array_merge($sets,$authsets);
}

// init
$set = NULL;
$pano = NULL;

// parse
foreach ($sets as &$_set) {
    // auth
    $_set->grant = false;
    if (!isset($_set->auth))
        $_set->auth = false;
    // default order
    foreach ($_set->views as $v => &$_view) {
        $_view->pid = $v;
        if (!isset($_view->order))
            $_view->order = 0;
    }
    // set found
    if ($_set->path == $s) {
        $set = $_set;
        // first view
        if (is_null($p)) {
            $p = 0;
            usort($set->views,_sortViews);
        }
        if (isset($set->views[$p]))
            $pano = $set->views[$p];
    }
}

// existence
$exists = (!is_null($set) && !is_null($pano));

// default
if (!$exists && !isset($_GET['s']) && !isset($_GET['p'])) {
    $exists = true;
    $set = $sets[0];
    usort($set->views,_sortViews);
    $pano = $set->views[0];
}

// access submitted
if (isset($_SERVER['PHP_AUTH_USER'])) {
    if ($_SERVER['PHP_AUTH_USER'] == $set->acl->usr && $_SERVER['PHP_AUTH_PW'] == $set->acl->pwd) {
        $set->grant = true;
        $sets = array($set);
    }
}

// access needed
$cancel = false;
if ($set->auth && (!$set->grant || !isset($_SERVER['PHP_AUTH_USER']))) {
    header('WWW-Authenticate: Basic realm="Restricted Area"');
    header('HTTP/1.0 401 Unauthorized');
    $cancel = true;
    $exists = false;
}

?>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>FOXEL | Basic Demonstration<?php if ($exists) print ' // '.$set->name.', '.$pano->caption; ?></title>
    <meta name="description" content="Expert in Stereophotogrammetry and 3D Environment Digitizing" />
    <meta name="viewport" content="width=device-width,height=device-height,user-scalable=no,minimum-scale=1.0,maximum-scale=1.0" />
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
    <link rel="stylesheet" type="text/css" media="all" href="../lib/freepano/css/jquery.toastmessage.css" />
    <link rel="stylesheet" type="text/css" media="all" href="js/jquery.mCustomScrollbar/jquery.mCustomScrollbar.css" />
    <link rel="stylesheet" type="text/css" media="all" href="../lib/freepano/css/main.css" />
    <link rel="stylesheet" type="text/css" media="all" href="css/basic.css" />
    <script type="text/javascript" src="../lib/freepano/js/jquery-2.1.0.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/touchHandler.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/jquery.mousewheel.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/jquery.toastmessage.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/jquery.easing-1.3.min.js"></script>
    <script type="text/javascript" src="js/jquery.mCustomScrollbar/jquery.mCustomScrollbar-3.0.2.min.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/notify.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/watch-1.3.0.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/three.js/three.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/jquery.freepano.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/jquery.freepano.controls.js"></script>
    <script type="text/javascript">
        <?php if (!$cancel && $exists): ?>
            var cfg = {
                path: '<?php print ($set->auth?'restricted':'tiles').'/'.$set->path.'/'.$pano->pid; ?>',
                src: '<?php print $pano->src; ?>',
                override: <?php print (isset($pano->override)?json_encode($pano->override):'{}'); ?>
            };
        <?php endif; ?>
    </script>
    <script type="text/javascript" src="js/basic.js"></script>
</head>

<body>

<div id="wrapper">

    <?php if ($exists): ?>
        <div id="pano" class="freepano"></div>
    <?php elseif ($cancel): ?>
        <div id="pnf">Access Unauthorized</div>
    <?php else: ?>
        <div id="pnf">Panorama Not Found</div>
    <?php endif; ?>

<?php if (!$cancel && $exists): ?>

    <div id="nav">
        <div class="shade"></div>
        <div class="tab">
            <div class="lay"></div>
            <div>More Demos</div>
        </div>
        <div class="main">
            <div class="scrollable">
                <div class="area">
                <?php
                    // sets
                    foreach ($sets as &$_set):
                        $_as = $_set->path == $set->path;
                        // auth
                        if ($_set->auth && !$_set->grant)
                            continue;
                        // ordering
                        usort($_set->views,_sortViews);
                ?>
                    <div class="dataset">
                        <div class="set <?php if ($_as) print('active'); ?>"><?php print $_set->name; ?></div>
                    <?php
                        // views
                        foreach ($_set->views as &$_view):
                            $_av = $_view->pid == $pano->pid;
                    ?>
                        <div class="pano"><a href="./?s=<?php print $_set->path; ?>&p=<?php print $_view->pid; ?>"><img <?php if ($_as && $_av) print('class="active"'); ?> src="<?php print ($set->auth?'restricted':'tiles'); ?>/<?php print $_set->path.'/'.$_view->pid; ?>/preview.png" alt="<?php print $_set->name.', '.$_view->caption; ?>" title="<?php print $_set->name.', '.$_view->caption; ?>" /></a></div>
                    <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="shade"></div>
        <div class="main">
            <div class="caption">
                <div><?php print $set->name; ?></div>
                <div><?php print $pano->caption; ?></div>
            </div>
            <div class="scroll">
                <div><img src="img/scroll.png" alt="" width="18" /></div>
            </div>
            <div class="logo attribution">
                <a href="http://foxel.ch/" target="_blank"><img src="../lib/freepano/img/foxel.png" alt="FOXEL" width="71" height="18" /></a>
                <div class="cc-by-sa">
                    <img src="img/cc.large.png" width="16" alt="CC" />
                    <img src="img/by.large.png" width="16" alt="BY" />
                    <img src="img/sa.large.png" width="16" alt="SA" />
                </div>
            </div>
            <div class="more">
                <div class="wrap">
                    <div class="col foxel">
                        <a href="http://foxel.ch/" target="_blank"><img src="img/foxel.png" alt="FOXEL" width="320" height="54" /></a>
                    </div>
                    <div class="col text">
                        <div class="title">Expert in Stereophotogrammetry<br />and 3D Environment Digitizing</div>
                        <p>Our mission is to develop technological solutions dedicated to 3D environment digitizing using technologies based on the CERN OHL license and other GNU GPL compatible licenses.</p>
                        <p>Our model and general approach predominantly strives for our Clients to reappropriate control of their data and further, their numeric territory.</p>
                        <p>Read more on <a href="http://foxel.ch/" target="_blank">http://foxel.ch</a></p>
                        <div class="notice">
                            <div class="cc-by-sa">
                                <a href="http://foxel.ch/license" target="_blank"><img src="img/cc.large.png" width="20" alt="CC" title="Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA)" /></a>
                                <a href="http://foxel.ch/license" target="_blank"><img src="img/by.large.png" width="20" alt="BY" title="Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA)" /></a>
                                <a href="http://foxel.ch/license" target="_blank"><img src="img/sa.large.png" width="20" alt="SA" title="Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA)" /></a>
                            </div>
                            <div class="copyright">
                                CC BY-SA 4.0&nbsp;
                                <a href="http://foxel.ch/license" target="_blank">Usage and Attribution</a>
                                &nbsp;&copy;&nbsp;
                                2013-2014 <a href="http://foxel.ch/" target="_blank">FOXEL SA</a>
                            <?php if (isset($pano->notices) && is_array($pano->notices)): ?>
                                <div class="notes">
                                    <?php foreach ($pano->notices as $notice): ?>
                                        <div class="note"><?php print $notice; ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>
    </footer>

<?php else: ?>

    <footer>
        <div class="main">
            <div class="logo attribution">
                <a href="http://foxel.ch/" target="_blank"><img src="../lib/freepano/img/foxel.png" alt="FOXEL" width="71" height="18" /></a>
            </div>
        </div>
    </footer>

<?php endif; ?>

</div>

</body>
</html>
