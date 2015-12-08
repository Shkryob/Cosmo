<?php

require_once 'autoload.php';
require_once 'Cosmo.class.php';
$Cosmo = new Cosmo($pdo, $prefix, $salt);

session_start();

// Initialize variables
$angularModules = '';
$directives = array();
$classes = '';
$minifyScripts = 'min/?f=';
$minifyCSS = 'min/?f=';
$scripts = '';
$CSS = '';
$coreScripts = array();

// Log user in if they have a cookie
if(isset($_COOKIE['usersID']) && $_COOKIE['usersID'] && $_COOKIE['token'])
{
    // Validate token
    if($Cosmo->tokensRead($_COOKIE['usersID'], $_COOKIE['token']))
    {
        $usersID = $_COOKIE['usersID'];
        $username = $_COOKIE['username'];
        $roleRecord = $Cosmo->usersRead($usersID);
        $role = $roleRecord['role'];

        // Delete one-use token, issue a new one
        // todo: fix this so it doesn't break every refresh
        //$Cosmo->tokensDelete($username, $_COOKIE['token']);
        //$token = $Cosmo->tokensCreate($username);
        $token = $_COOKIE['token'];
        //setcookie('token', $token, time()+60*60*24*90); // Set cookie to expire in 90 days

        $coreScripts[] = FOLDER."core/js/3rd-party/angular-file-upload-shim.min.js"; // Breaks IE9, so only load it for admins
    }
}

// Load official Angular files
$coreScripts[] = FOLDER."core/js/angular/angular.min.js";
$coreScripts[] = FOLDER."core/js/angular/angular-animate.min.js";
$coreScripts[] = FOLDER."core/js/angular/angular-touch.min.js";
$coreScripts[] = FOLDER."core/js/angular/angular-route.min.js";
$coreScripts[] = FOLDER."core/js/angular/angular-resource.min.js";

// Load the Cosmo module
$coreScripts[] = FOLDER."core/js/cosmo.js";

// Load the Cosmo admin panel files
$coreScripts[] = FOLDER."core/js/admin-panel/admin-panel.js";
$coreScripts[] = FOLDER."core/js/admin-panel/block.js";
$coreScripts[] = FOLDER."core/js/admin-panel/content-list.js";
$coreScripts[] = FOLDER."core/js/admin-panel/files.js";
$coreScripts[] = FOLDER."core/js/admin-panel/login-registration.js";
$coreScripts[] = FOLDER."core/js/admin-panel/menu.js";
$coreScripts[] = FOLDER."core/js/admin-panel/module.js";
$coreScripts[] = FOLDER."core/js/admin-panel/page.js";
$coreScripts[] = FOLDER."core/js/admin-panel/profile.js";
$coreScripts[] = FOLDER."core/js/admin-panel/reset-password.js";
$coreScripts[] = FOLDER."core/js/admin-panel/revisions.js";
$coreScripts[] = FOLDER."core/js/admin-panel/settings.js";
$coreScripts[] = FOLDER."core/js/admin-panel/theme.js";
$coreScripts[] = FOLDER."core/js/admin-panel/users.js";

// Load the directive files
$coreScripts[] = FOLDER."core/js/directives/csAnchor.js";
$coreScripts[] = FOLDER."core/js/directives/csAudio.js";
$coreScripts[] = FOLDER."core/js/directives/csBgImage.js";
$coreScripts[] = FOLDER."core/js/directives/csBlock.js";
$coreScripts[] = FOLDER."core/js/directives/csContent.js";
$coreScripts[] = FOLDER."core/js/directives/csGallery.js";
$coreScripts[] = FOLDER."core/js/directives/csImage.js";
$coreScripts[] = FOLDER."core/js/directives/csLogo.js";
$coreScripts[] = FOLDER."core/js/directives/csMenu.js";
$coreScripts[] = FOLDER."core/js/directives/csMovie.js";
$coreScripts[] = FOLDER."core/js/directives/csNotification.js";
$coreScripts[] = FOLDER."core/js/directives/csResult.js";
$coreScripts[] = FOLDER."core/js/directives/csSearch.js";
$coreScripts[] = FOLDER."core/js/directives/csSlogan.js";
$coreScripts[] = FOLDER."core/js/directives/csTable.js";
$coreScripts[] = FOLDER."core/js/directives/csTitle.js";
$coreScripts[] = FOLDER."core/js/directives/csWysiwyg.js";
$coreScripts[] = FOLDER."core/js/directives/fluidvids.js";

// Load the factory files
$coreScripts[] = FOLDER."core/js/factories/hooks.js";
$coreScripts[] = FOLDER."core/js/factories/page.js";
$coreScripts[] = FOLDER."core/js/factories/plaintext.js";
$coreScripts[] = FOLDER."core/js/factories/responsive.js";
$coreScripts[] = FOLDER."core/js/factories/rest.js";
$coreScripts[] = FOLDER."core/js/factories/users.js";

// Load the filter files
$coreScripts[] = FOLDER."core/js/filters/image-size.js";
$coreScripts[] = FOLDER."core/js/filters/theme-files.js";
$coreScripts[] = FOLDER."core/js/filters/timestamp.js";
$coreScripts[] = FOLDER."core/js/filters/titlecase.js";

// Load the front-end controller files
$coreScripts[] = FOLDER."core/js/front-end/comments.js";
$coreScripts[] = FOLDER."core/js/front-end/content.js";
$coreScripts[] = FOLDER."core/js/front-end/html.js";
$coreScripts[] = FOLDER."core/js/front-end/link.js";
$coreScripts[] = FOLDER."core/js/front-end/url.js";
$coreScripts[] = FOLDER."core/js/front-end/wysiwyg.js";

// 3rd party scripts
$coreScripts[] = FOLDER."core/js/3rd-party/angular-file-upload.min.js";
$coreScripts[] = FOLDER."core/js/3rd-party/angular-translate.min.js";
$coreScripts[] = FOLDER."core/js/3rd-party/angular-translate-loader-static-files.min.js";
// $coreScripts[] = FOLDER."core/js/3rd-party/angular-translate-storage-cookie.min.js";
$coreScripts[] = FOLDER."core/js/3rd-party/diff_match_patch.js";
$coreScripts[] = FOLDER."core/js/3rd-party/angular-ui-tree.min.js";


$minifyCSS .= FOLDER."core/css/cosmo-default-style.minify.css";

if ($developerMode) {
    foreach ($coreScripts as $script) {
        $scripts .= "<script src='" . $script . "'></script>\n\t";
    }
} else {
    $minifyScripts .= implode(',', $coreScripts);
}

// Load menus
$menus = $Cosmo->menusRead();

// Load theme files
$settings = $Cosmo->settingsRead();
$theme = $settings['theme'];

if(file_exists("themes/$theme/cosmo.json"))
{
    $themeJSON = json_decode(file_get_contents("themes/$theme/cosmo.json"));

    // Add to module list
    if(!empty($themeJSON->module))
        $angularModules .= ",\n\t\t'". $themeJSON->module ."'";

    // Get all Directives
    if(!empty($themeJSON->directives)){
        foreach($themeJSON->directives as $directive)
            $directives[] = $directive;
    }

    // Get all classes
    if(!empty($themeJSON->classes)){
        $classes .= $themeJSON->name . ":;";
        foreach($themeJSON->classes as $class)
            $classes .= $class . ";";
    }

    // Check if there is are Javascript files for this theme
    if(!empty($themeJSON->scripts)){
        foreach($themeJSON->scripts as $script){
            if(strpos($script, '//') !== FALSE)
                $scripts .= "<script src='". $script ."'></script>\n\t"; // External js files
            else if(!$developerMode && (strpos($script, '.min.') !== FALSE || strpos($script, '.minify.') !== FALSE)) // Minify and combine script
                $minifyScripts .= ",".FOLDER."themes/$theme/". $script;
            else
                $scripts .= "<script src='"."themes/$theme/". $script ."'></script>\n\t"; // File shouldn't be minified
        }
    }

    // Check if there is are CSS files for this theme
    if(!empty($themeJSON->css)){
        foreach($themeJSON->css as $css){
            if(strpos($css, '//') !== FALSE)
                $CSS .= "<link href='". $css ."' rel='stylesheet' type='text/css'>\n\t"; // External style sheets
            else if(!$developerMode && (strpos($css, '.min.') !== FALSE || strpos($css, '.minify.') !== FALSE)) // Minify and combine script
                $minifyCSS .= ",".FOLDER."themes/$theme/". $css;
            else
                $CSS .= "<link href='"."themes/$theme/". $css ."' rel='stylesheet' type='text/css'>\n\t"; // File can't be minified
        }
    }
}

// Load all modules and their JS/CSS files
$stmt = $pdo->prepare('SELECT * FROM '.$prefix.'modules WHERE status=?');
$data = array('active');
$stmt->execute($data);
$stmt->setFetchMode(PDO::FETCH_ASSOC);
while($row = $stmt->fetch())
{
    $folder = $row['module'];

    if(file_exists("modules/$folder/cosmo.json"))
    {
        $moduleJSON = json_decode(file_get_contents("modules/$folder/cosmo.json"));

        // Add to module list
        if(!empty($moduleJSON->module))
            $angularModules .= ",\n\t\t'". $moduleJSON->module ."'";

        // Get all directives
        if(!empty($moduleJSON->directives)){
            foreach($moduleJSON->directives as $directive)
                $directives[] = $directive;
        }

        // Get all classes
        if(!empty($moduleJSON->classes)){
            $classes .= $moduleJSON->name . ":;";
            foreach($moduleJSON->classes as $class)
                $classes .= $class . ";";
        }

        // Check if there is are Javascript files for this script
        if(!empty($moduleJSON->scripts)){
            foreach($moduleJSON->scripts as $script){
                if(strpos($script, '//') !== FALSE)
                    $scripts .= "<script src='". $script ."'></script>\n\t"; // External file
                else if(!$developerMode && (strpos($script, '.min.') !== FALSE || strpos($script, '.minify.')) !== FALSE)
                    $minifyScripts .= ",".FOLDER."modules/$folder/". $script; // Minify and combine script
                else
                    $scripts .= "<script src='"."modules/$folder/". $script ."'></script>\n\t"; // File can't be minified
            }
        }

        // Check if there are CSS files for this module
        if(!empty($moduleJSON->css)){
            foreach($moduleJSON->css as $css){
                if(strpos($css, '//') !== FALSE)
                    $CSS .= "<link href='". $css ."' rel='stylesheet'>\n\t"; // External stylesheet
                else if(!$developerMode && (strpos($css, '.min.') !== FALSE || strpos($css, '.minify.') !== FALSE))
                    $minifyCSS .= ",".FOLDER."modules/$folder/". $css; // Minify and combine
                else
                    $CSS .= "<link href='"."modules/$folder/". $css ."' rel='stylesheet'>\n\t"; // File can't be minified
            }
        }
    }
}

// Get initial content for social
$content = $Cosmo->contentRead($_SERVER['REQUEST_URI']);

?>
