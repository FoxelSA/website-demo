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
 * Contributor(s):
 *
 *      Luc Deschenaux <l.deschenaux@foxel.ch>
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


// request
$s = isset($_GET['s']) ? trim(strtolower($_GET['s'])) : NULL;
$p = isset($_GET['p']) ? (int)trim($_GET['p']) : 0;

// read json
$json = json_decode(file_get_contents('config.json'));
$sets = &$json->config->sets;

// init
$setIndex = 0;
$panoIndex = 0;

// look for set
if (!is_null($s)) {
    foreach ($sets as $i => &$set) {
        if ($set->path == $s) {
            $setIndex = $i;
            $panoIndex = $p;
            if (!isset($set->views[$p]))
                $panoIndex = 0;
            break;
        }
    }
}

// panorama
$set = &$sets[$setIndex];
$pano = &$set->views[$panoIndex];

?>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>FOXEL | Basic Demonstration // <?php print $set->name; ?>, <?php print $pano->caption; ?></title>
    <meta name="description" content="Expert in Stereophotogrammetry and 3D Environment Digitizing" />
    <meta name="viewport" content="width=device-width,height=device-height,user-scalable=no,minimum-scale=1.0,maximum-scale=1.0" />
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
    <link rel="stylesheet" type="text/css" media="all" href="../lib/freepano/css/jquery.toastmessage.css" />
    <link rel="stylesheet" type="text/css" media="all" href="../lib/freepano/css/main.css" />
    <link rel="stylesheet" type="text/css" media="all" href="css/basic.css" />
    <script type="text/javascript" src="../lib/freepano/js/jquery-2.1.0.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/touchHandler.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/jquery.mousewheel.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/jquery.toastmessage.js"></script>
    <script type="text/javascript" src="js/jquery.easing-1.3.min.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/notify.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/three.js/three.js"></script>
    <script type="text/javascript" src="../lib/freepano/js/jquery.freepano.js"></script>
    <script type="text/javascript">
        var tiles = {
            path: '<?php print $set->path.'/'.$panoIndex; ?>',
            src: '<?php print $pano->src; ?>'
        };
    </script>
    <script type="text/javascript" src="js/basic.js"></script>
</head>

<body>

<div id="pano" class="freepano"></div>

<div id="nav">
    <div class="shade"></div>
    <div class="tab">
        <div class="lay"></div>
        <div>Navigation</div>
    </div>
    <div class="main">
        <div class="scrollable">
        <?php foreach ($sets as $id => &$dataset): ?>
            <div class="dataset">
                <div class="set"><?php print $dataset->name; ?></div>
            <?php foreach ($dataset->views as $iv => &$view): ?>
                <div class="pano"><a href="./?s=<?php print $dataset->path; ?>&p=<?php print $iv; ?>"><img <?php if ($id==$setIndex && $iv==$panoIndex) print('class="active"'); ?> src="tiles/<?php print $dataset->path.'/'.$iv; ?>/preview.png" alt="<?php print $dataset->name; ?>, <?php print $view->caption; ?>" title="<?php print $dataset->name; ?>, <?php print $view->caption; ?>" /></a></div>
            <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
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
            <img src="img/scroll.png" alt="" width="18" />
        </div>
        <div class="logo attribution">
            <a href="http://foxel.ch/" target="_blank"><img src="../lib/freepano/img/foxel.png" alt="FOXEL" width="71" height="18" /></a>
        </div>
        <div class="more">
            <div class="wrap">
                <div class="col logo">
                    <a href="http://foxel.ch/" target="_blank"><img src="img/foxel.png" alt="FOXEL" width="360" height="60" /></a>
                </div>
                <div class="col text">
                    <div class="title">Expert in Stereophotogrammetry<br />and 3D Environment Digitizing</div>
                    <p>Our mission is to develop technological solutions dedicated to 3D environment digitizing using technologies based on the CERN OHL license and other GNU GPL compatible licenses.</p>
                    <p>Our model and general approach predominantly strives for our Clients to reappropriate control of their data and further, their numeric territory.</p>
                    <p>Read more on <a href="http://foxel.ch/" target="_blank">http://foxel.ch</a></p>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
