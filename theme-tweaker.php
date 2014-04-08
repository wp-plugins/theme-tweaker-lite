<?php
/*
  Plugin Name: Theme Tweaker
  Plugin URI: http://www.thulasidas.com/plugins/theme-tweaker
  Description: <em>Lite Version</em>: Tweak your theme colors (yes, any theme) with no CSS stylesheet editing. To tweak your theme, go to <a href="themes.php?page=theme-tweaker.php"> Appearance (or Design) &rarr; Theme Tweaker</a>.
  Version: 4.10
  Author: Manoj Thulasidas
  Author URI: http://www.thulasidas.com
 */

/*
  Copyright (C) 2008 www.ads-ez.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (class_exists("ThemeTweaker")) {
  die("<strong><em>Theme Tweaker Pro</em></strong> seems to be active.<br />Please use the Pro version, or deactivate it before activating <strong><em>Theme Tweaker Lite</em></strong>.");
}

if (!function_exists('str_ireplace')) {

  function str_ireplace($find, $replace, $string) {
    if (!is_array($find)) {
      $find = array($find);
    }

    if (!is_array($replace)) {
      if (!is_array($find)) {
        $replace = array($replace);
      }
      else {
        // this will duplicate the string into an array the size of $find
        $c = count($find);
        $rString = $replace;
        unset($replace);
        for ($i = 0; $i < $c; $i++) {
          $replace[$i] = $rString;
        }
      }
    }
    foreach ($find as $fKey => $fItem) {
      $between = explode(strtolower($fItem), strtolower($string));
      $pos = 0;
      foreach ($between as $bKey => $bItem) {
        $between[$bKey] = substr($string, $pos, strlen($bItem));
        $pos += strlen($bItem) + strlen($fItem);
      }
      $string = implode($replace[$fKey], $between);
    }
    return($string);
  }

}

if (!class_exists("ThemeTweaker")) {

  require_once('EzOptions.php');

  class ThemeTweaker {

    var $isPro, $proStr;
    var $slug, $domain, $plgDir, $plgURL, $ezTran, $ezAdmin, $myPlugins, $options;

    //constructor
    function ThemeTweaker() {
      $this->plgDir = dirname(__FILE__);
      $this->plgURL = plugin_dir_url(__FILE__);
      if (is_admin()) {
        require_once($this->plgDir . '/EzTran.php');
        $this->domain = $this->slug = 'theme-tweaker';
        $this->ezTran = new EzTran(__FILE__, "Theme Tweaker", $this->domain);
        $this->ezTran->setLang();
      }
      $this->isPro = file_exists($this->plgDir . '/pro/pro.php');
      $this->strPro = ' Lite';
      $this->session_start();
      if ($this->isPro) {
        $this->strPro = ' Pro';
      }
      if (isset($_POST['saveCSS']) || isset($_POST['saveChild'])) {
        $file = 'style.css';
        if (isset($_POST['saveCSS'])) {
          $str = $_SESSION['strCSS'];
        }
        if (isset($_POST['saveChild'])) {
          $str = $_SESSION['strChild'];
        }
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header("Content-Transfer-Encoding: ascii");
        header('Expires: 0');
        header('Pragma: no-cache');
        ob_start();
        print (htmlspecialchars_decode($str));
        ob_end_flush();
        exit(0);
      }
    }

    function session_start() {
      if (!session_id())
        @session_start();
    }

    function init() {
      $this->getAdminOptions();
    }

    //Returns an array of admin options
    function getAdminOptions() {
      $mThemeName = get_option('stylesheet');
      $mOldKey = $mThemeName . '_OldColors';
      $mNewKey = $mThemeName . '_NewColors';
//      $mPreKey = $mThemeName . '_Preview';
//      $mActKey = $mThemeName . '_Activate';
//      $mFooter = $mThemeName . '_Footer';
      $mPreKey = 'preview';
      $mActKey = 'activate';
      $mFooter = 'footer_link';

      $mCSSKey = $mThemeName . '_Tweaked';
      $mTrmKey = $mThemeName . '_Trim';
      $mOptions = "themeTweaker" . $mThemeName;
      $themeTweakerAdminOptions = array($mOldKey => array(),
          $mNewKey => array(),
          $mPreKey => true,
          $mActKey => false,
          $mFooter => true,
          'kill_invites' => false,
          'kill_rating' => false,
          $mCSSKey => '',
          $mTrmKey => '');
      $this->options = get_option($mOptions);
      if (!empty($this->options)) {
        foreach ($this->options as $key => $option) {
          $themeTweakerAdminOptions[$key] = $option;
        }
      }
      update_option($mOptions, $themeTweakerAdminOptions);
      return $themeTweakerAdminOptions;
    }

    function tmpColor($hex) {
      $newhex = '%' . substr($hex, -6) . '%';
      return $newhex;
    }

    function negColor($hex) {
      $newhex = dechex(hexdec('ffffff') - hexdec($hex));
      for ($i = strlen($newhex); $i < 6; $i++) {
        $newhex = '0' . $newhex;
      }
      $newhex = strtoupper('#' . $newhex);
      return $newhex;
    }

    function greyColor($hex) {
      $r = hexdec($hex[1] . $hex[2]);
      $g = hexdec($hex[3] . $hex[4]);
      $b = hexdec($hex[5] . $hex[6]);
      $y = 0.3 * $r + 0.59 * $g + 0.11 * $b;
      $yy = dechex($y);
      if (strlen($yy) == 1) {
        $yy = '0' . $yy;
      }
      $newhex = '#' . $yy . $yy . $yy;
      return $newhex;
    }

    function sepia($hex) {
      $r = hexdec($hex[1] . $hex[2]);
      $g = hexdec($hex[3] . $hex[4]);
      $b = hexdec($hex[5] . $hex[6]);
      $y = 0.3 * $r + 0.59 * $g + 0.11 * $b;
      $s = $y / 255;
      $r = min($y + 77 * $s, 255);
      $g = min($y + 13 * $s, 255);
      $b = $y;

      $r = dechex($r);
      if (strlen($r) == 1) {
        $r = '0' . $r;
      }
      $g = dechex($g);
      if (strlen($g) == 1) {
        $g = '0' . $g;
      }
      $b = dechex($b);
      if (strlen($b) == 1) {
        $b = '0' . $b;
      }
      $newhex = '#' . $r . $g . $b;
      return $newhex;
    }

    function random($hex) {
      $r = rand(1, 255);
      $g = rand(1, 255);
      $b = rand(1, 255);
      $r = dechex($r);
      if (strlen($r) == 1) {
        $r = '0' . $r;
      }
      $g = dechex($g);
      if (strlen($g) == 1) {
        $g = '0' . $g;
      }
      $b = dechex($b);
      if (strlen($b) == 1) {
        $b = '0' . $b;
      }
      $newhex = '#' . $r . $g . $b;
      return $newhex;
    }

    function mapFunc($array, $func) {
      $newarray = array();
      foreach ($array as $val) {
        $newarray[] = $this->$func($val);
      }
      return $newarray;
    }

    function shortenColors($str) {
      // replace long hex colors with short ones, if possible
      $hex = array('00', '11', '22', '33', '44', '55', '66', '77',
          '88', '99', '00', 'AA', 'BB', 'CC', 'EE', 'FF',
          'aA', 'bB', 'cC', 'eE', 'fF',
          'Aa', 'Bb', 'Cc', 'Ee', 'Ff',
          'aa', 'bb', 'cc', 'ee', 'ff');

      $colorRE = '/#(' . $hex[0];
      foreach ($hex as $key => $val) {
        if ($key > 0) {
          $colorRE .= '|' . $hex[$key];
        }
      }
      $colorRE .= '){3}/';

      // find long colors
      preg_match_all($colorRE, $str, $colors);
      // all the matches are in the first array of the results
      $colors = $colors[0];
    }

    /* break it up into two: getColors and formatCSS
      use somethig like
      preg_replace("/(<\/?)(\w+)([^>]*>)/e",
      "'\\1'.strtoupper('\\2').'\\3'",
      $html_body);
      which would capitalize all HTML tags in the input text.
     */

    function getColors(&$str) {
      // replace the color mnemonics in $str
      $cname = array('aqua', 'black', 'blue', 'fuchsia',
          'gray', 'green', 'lime', 'maroon',
          'navy', 'olive', 'purple', 'red',
          'silver', 'teal', 'white', 'yellow');

      $hname = array('#00FFFF', '#000000', '#0000FF', '#FF00FF',
          '#808080', '#008000', '#00FF00', '#800000',
          '#000080', '#808000', '#800080', '#FF0000',
          '#C0C0C0', '#008080', '#FFFFFF', '#FFFF00');
      foreach ($cname as $key => $val) {
        $reg[$key] = '/\b';
        for ($i = 0; $i < strlen($val); $i++) {
          $reg[$key] .= '[' . $val[$i] . strtoupper($val[$i]) . ']';
        }
        $reg[$key] .= '\b/';
      }
      $str = preg_replace($reg, $hname, $str);

      // Now find the hex color names
      $colorRE = "/#[a-fA-F0-9]{6}|#[a-fA-F0-9]{3}\b/";
      $tstr = strtoupper($str);
      preg_match_all($colorRE, $tstr, $colors);

      // all the matches are in the first array of the results
      $colors = array_unique($colors[0]);

      // search and replace non-standard names to std long names
      $search = array();
      $replace = array();
      $matches = array();
      foreach ($colors as $val) {
        if (strlen($val) == 4) {
          $search[] = '/' . $val . '\b/i';
          // short color - make it long
          $tmp = '#';
          for ($i = 1; $i <= 3; $i++) {
            $tmp .= $val[$i] . $val[$i];
          }
          $val = $tmp;
          $val = strtoupper($val);
          $replace[] = $val;
        }
        $matches[] = strtoupper($val);
      }
      $colors = $matches;

      // this cannot be a str_replace because #000010 -> #000000010!
      $str = preg_replace($search, $replace, $str);

      // make unique and sort
      $colors = array_unique($colors);
      sort($colors);

      return $colors;
    }

    // make a color table
    function makeTable($colors0, $colors1) {
      $table = '<table style="border-spacing=0;border-collapse=collapse;margin-right:auto;margin-left:auto;vertical-algin:middle;">' . "\n";
      $table .= '<tr><td><b>Old Colors</b></td>' . "\n" .
              '<td><b>Tweaked Colors</b><br />Click to Modify</td></tr>';
      foreach ($colors0 as $key => $val) {
        $newcol = $colors1[$key];
        $name = substr($val, -6);
        $nopicker = '<input readonly="readonly" class="color {picker:false}" ' .
                'style="border:0px solid;" value="' . $val . '" title="Original Color [read only]"/>';
        $picker = '<input style="border:0px solid;" class="color {hash:true,caps:true,' .
                'pickerFaceColor:\'transparent\',pickerFace:3,pickerBorder:0,' .
                'pickerInsetColor:\'black\'}" onchange="document.getElementById(\'td_' .
                $name . '\').bgcolor = \'#\'+this.color" value="' .
                $newcol . '" name="in_' . $name . '" id="in_' . $name .
                '" title="Tweaked Color [Click to pick, or Type in RRGGBB]" />';

        $table .= '<tr><td style="background-color:' . $val . '">' . $nopicker . '</td>' . "\n" .
                '<td style="background-color:' . $newcol . '" id="td_' . $name . '">' .
                $picker . '</td></tr>' . "\n";
      }
      $table .= '</table>';
      return $table;
    }

    function initNewColors($colors, $newcol) {
      $js = '';
      foreach ($newcol as $key => $val) {
        $name = substr($colors[$key], -6);
        $js .= "document.getElementById('td_" . $name . "').bgcolor='" . $val . "';" .
                "document.getElementById('in_" . $name . "').color.fromString('" . $val . "');";
      }
      return $js;
    }

    function initRandomColors($colors) {
      $js = '';
      foreach ($colors as $key => $val) {
        $name = substr($val, -6);
        $js .= "rcol=random_color('hex'); " .
                "document.getElementById('td_" . $name . "').bgcolor=rcol;" .
                "document.getElementById('in_" . $name . "').color.fromString(rcol);";
      }
      return $js;
    }

    function patchURL($val) {
      // first, pick up the argument to the URL function
      $reg = '.*[uU][rR][lL]\(([^\)]+)\)';
      eregi($reg, $val, $url);
      $url = trim($url[1]);
      $newurl = get_theme_root_uri() . '/' . get_option('stylesheet') .
              '/' . trim($url, '"\'');
      $val = str_replace($url, $newurl, $val);
      return trim($val);
    }

    function trimCSS1($css) {
      // beginning part
      // remove comments first
      $reg = "@/\*(?:.|[\r\n])*?\*/@";
      $css = preg_replace($reg, '', $css);
      // remove @import line
      $css = preg_replace('/\@.*?;/', '', $css);
      $reg = '/.*\{/';
      preg_match($reg, $css, $start);
      $trim = $start[0];
      $reg = '/[^;^{]*#[a-fA-F0-9]{6}[^;\b]*;/';
      preg_match_all($reg, $css, $clines);
      $clines = $clines[0];
      if (count($clines) > 0) {
        foreach ($clines as $val) {
          $pos = stripos($val, 'url(');
          if ($pos === false) {
            $trim .= trim($val);
          }
          else {
            $trim .= $this->patchURL($val);
          }
        }
        $trim .= "}\n";
      }
      else {
        $trim = '';
      }
      return $trim;
    }

    function trimCSS($stylestr) {
      // Try to trim the CSS string so that only the modified lines are kept.
      // look for CSS blocks
      $reg = '/[\s]*[a-zA-Z0-9\.# -_:@\s,]+\{[^\}]+\}/';
      preg_match_all($reg, $stylestr, $css);
      $css = $css[0];
      $output = '';
      // loop over the css blocks and find the styles with colors
      foreach ($css as $val) {
        $trim = $this->trimCSS1($val);
        if ($trim != '') {
          $output .= trim($trim) . "\n";
        }
      }
      return $output;
    }

    function getComments($stylestr) {
      $start = 0;
      $end = strpos($stylestr, '*/');
      return substr($stylestr, $start, $end);
    }

    function makeButtons($colors0, $colors1) {
      $mThemeName = get_option('stylesheet');
      $table = '';
      $table .= '<table style="margin-right:auto;margin-left:auto;text-align:center;">' . "\n" . '<tr><td>';

      // Reset
      $table .= '</td></tr>' . "\n" . '<tr><td>';
      $table .= '<input type="button" style="width:100%;" name="reset" value="Reset Colors" ';
      $table .= 'title="Reset the colors to the original colors of ';
      $table .= $mThemeName . '" ';
      $table .= 'onclick=" ';
      $table .= $this->initNewColors($colors0, $colors0);
      $table .= '" />';

      // invert colors
      $table .= '</td></tr>' . "\n" . '<tr><td>';
      $newcol = $this->mapFunc($colors0, 'negColor');
      $table .= '<input type="button" style="width:100%;" name="negative" value="Invert Colors" ';
      $table .= 'title="Color negatives of the original colors in ';
      $table .= $mThemeName . '" ';
      $table .= 'onclick=" ';

      $table .= $this->initNewColors($colors0, $newcol);
      $table .= '" />';

      // grey scale
      $table .= '</td></tr>' . "\n" . '<tr><td>';
      $newcol = $this->mapFunc($colors0, 'greyColor');
      $table .= '<input type="button" style="width:100%;" name="grey" value="Black &amp; White" ';
      $table .= 'title="Desaturate to grey scales of the original colors of ';
      $table .= $mThemeName . '" ';
      $table .= 'onclick=" ';
      $table .= $this->initNewColors($colors0, $newcol);
      $table .= '" />';

      // grey scale negative
      $table .= '</td></tr>' . "\n" . '<tr><td>';
      $newcol = $this->mapFunc($colors0, 'negColor');
      $newcol = $this->mapFunc($newcol, 'greyColor');
      $table .= '<input type="button" style="width:100%;" name="greyneg" value="B&amp;W Negative" ';
      $table .= 'title="Negative of the desaturated colors to the original colors of ';
      $table .= $mThemeName . '" ';
      $table .= ' onclick=" ';
      $table .= $this->initNewColors($colors0, $newcol);
      $table .= '" />';

      // sepia
      $table .= '</td></tr>' . "\n" . '<tr><td>';
      $newcol = $this->mapFunc($colors0, 'sepia');
      $table .= '<input type="button" style="width:100%;" name="sepia" value="Sepia Effect" ';
      $table .= 'title="Generate sepia colours out of the original colors of ';
      $table .= $mThemeName . '" ';
      $table .= 'onclick=" ';
      $table .= $this->initNewColors($colors0, $newcol);
      $table .= '" />';

      // random colors
      $table .= '</td></tr>' . "\n" . '<tr><td>';
      $table .= '<input type="button" style="width:100%;" name="random" value="Random Colors" ';
      $table .= 'title="Generate random colors while keeping the styles of ';
      $table .= $mThemeName . '" ';
      $table .= 'onclick=" ';
      $table .= $this->initRandomColors($colors0);
      $table .= '" />';

      // table closing tags
      $table .= '</td></tr>' . "\n" . '</table>';
      return $table;
    }

    function setOptionValues() {
      $error = EzBaseOption::setValues($this->options, $this->ezOptions);
      if (WP_DEBUG && !empty($error)) {
      //   echo "<div class='error'>$error</div>";
      }
    }

    function mkEzOptions() {
      if (!empty($this->ezOptions)) {
        return;
      }
      $o = new EzCheckBox('preview');
      $o->desc = __('Preview the new color scheme (Only Administrators will see the changes)', 'theme-tweaker');
      $o->before = "&nbsp;&nbsp;";
      $o->after = "<br />";
      $this->ezOptions['preview'] = clone $o;

      $o->name = "activate";
      $o->desc = __('Activate the new color scheme (All users will see the changes)', 'theme-tweaker');
      $this->ezOptions["activate"] = clone $o;

      $o->name = "footer_link";
      $o->desc = "<span style='color:red'>" . __('Suppress the tiny credit link at the bottom of your blog pages. (Please consider showing it if you would like to support this plugin. Thanks!)', 'theme-tweaker') . "</span>";
      $this->ezOptions["footer_link"] = clone $o;
    }

    //Prints out the admin page
    function printAdminPage() {
      // if translating, print translation interface
      if ($this->ezTran->printAdminPage()) {
        return;
      }
      require_once($this->plgDir . '/myPlugins.php');
      $slug = $this->slug;
      $plg = $this->myPlugins[$slug];
      $plgURL = $this->plgURL;
      require_once($this->plgDir . '/EzAdmin.php');
      $this->ezAdmin = new EzAdmin($plg, $slug, $plgURL);
      $this->ezAdmin->plgFile = __FILE__;
      if ($this->options['kill_author']) {
        $this->ezAdmin->killAuthor = true;
      }
      $this->ezAdmin->domain = $this->domain;
      $ez = $this->ezAdmin;

      $mThemeName = get_option('stylesheet');
      $mOldKey = $mThemeName . '_OldColors';
      $mNewKey = $mThemeName . '_NewColors';
//      $mPreKey = $mThemeName . '_Preview';
//      $mActKey = $mThemeName . '_Activate';
//      $mFooter = $mThemeName . '_Footer';
      $mPreKey = 'preview';
      $mActKey = 'activate';
      $mFooter = 'footer_link';

      $mCSSKey = $mThemeName . '_Tweaked';
      $mTrmKey = $mThemeName . '_Trim';
      $this->options = $this->getAdminOptions();

      // grab the theme stylesheet and print it here
      $stylefile = get_theme_root() . '/' . $mThemeName . '/style.css';
      $stylecontent = file_get_contents($stylefile);
      $colors0 = $this->getColors($stylecontent);

      // if the theme colors haven't changed, use the new colors from DB
      // else init them to the original colors
      if ($colors0 == $this->options[$mOldKey]) {
        $colors1 = $this->options[$mNewKey];
      }
      else {
        $colors1 = $colors0;
      }

      if (isset($_POST['update_themeTweakerSettings']) ||
              isset($_POST['saveCSS']) || isset($_POST['saveChild']) ||
              isset($_POST['clean_db'])) {
        // loop over the new color fields to get colors1
        foreach ($colors0 as $key => $val) {
          $name = 'in_' . substr($val, -6);
          if (isset($_POST[$name])) {
            $colors1[$key] = $_POST[$name];
          }
          else {
            $colors1[$key] = '#FFFFFF';
          }
        }
        // check activate and preview buttons
        if (isset($_POST['preview'])) {
          // echo 'Will Preview ' ;
          $this->options[$mPreKey] = true;
        }
        else {
          $this->options[$mPreKey] = false;
        }
        if (isset($_POST['activate'])) {
          $this->options[$mActKey] = true;
        }
        else {
          $this->options[$mActKey] = false;
        }
        if (isset($_POST['footer'])) {
          $this->options[$mFooter] = true;
        }
        else {
          $this->options[$mFooter] = false;
        }
        if (isset($_POST['kill_invites'])) {
          $this->options['kill_invites'] = $_POST['kill_invites'];
        }
        if (isset($_POST['kill_rating'])) {
          $this->options['kill_rating'] = $_POST['kill_rating'];
        }

        // need to replace in two steps, just incase colors0 and colors1 have overlaps
        $tmpcols = $this->mapFunc($colors0, 'tmpColor');

        // generate the new style
        $func = 'str_ireplace';

        $styletmp = $func($colors0, $tmpcols, $stylecontent);
        $stylestr = $func($tmpcols, $colors1, $styletmp);

        $this->options[$mOldKey] = $colors0;
        $this->options[$mNewKey] = $colors1;
        $this->options[$mCSSKey] = $stylestr;
        $trimstr = $this->trimCSS($stylestr);
        $this->options[$mTrmKey] = $trimstr;
        $mOptions = "themeTweaker" . $mThemeName;

        $this->mkEzOptions();
        $this->setOptionValues();

        foreach ($this->ezOptions as $k => $o) {
          if (isset($this->options[$k])) {
            $this->options[$k] = $o->get();
          }
          else {
            if (WP_DEBUG) {
              echo "<div class='error'>Warning: <code>option[$k]</code> is not defined, but <code>ezOption[$k]</code> exists!</div>";
            }
          }
        }

        update_option($mOptions, $this->options);
        if (isset($_POST['clean_db'])) {
          $this->cleanDB('themeTweaker');
        }
      }
      $this->mkEzOptions();
      $this->setOptionValues();
      echo '<script type="text/javascript" src="' . $this->plgURL . '/jscolor/jscolor.js"></script>';
      echo '<script type="text/javascript" src="' . $this->plgURL . '/wz_tooltip.js"></script>';
      ?>

      <div class="wrap" style="width:1000px">
        <h2>Theme Tweaker <a href="http://validator.w3.org/" target="_blank"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid HTML5" title="Theme Tweaker Admin Page is certified Valid HTML5" height="31" onmouseover="Tip('Theme Tweaker Admin Page is certified Valid HTML5, with no errors in the HTML code generated by the plugin.')" onmouseout="UnTip()" width="88" class="alignright"/></a></h2>

        <div id="status" class="updated"><?php
          if (isset($_POST['update_themeTweakerSettings'])) {
            echo $_SESSION['statUpdate'];
          }
          if (isset($_POST['clean_db'])) {
            echo $_SESSION['statClean'];
          }
          ?>
        </div>
        <?php
        $helpTags = array();
        $o = new EzHelpTag('help0');
        $o->title = __('Click for help', 'theme-tweaker');
        $o->tipTitle = __('How to Tweak Your Theme', 'theme-tweaker');
        $o->desc = __('Usage: How to tweak your theme colors.', 'theme-tweaker');
        $helpTags[] = $o;
        $help0 = array();
        $help0[] = sprintf(__('The color scheme of your current theme "%s" is shown in the table below as the first column under "Old Colors"', 'theme-tweaker'), $mThemeName);
        $help0[] = __('The new color scheme is in the second column under "Tweaked Colors (Click to modify)." Click on the new colors to modify them. You will get a color picker, or you can type in the new color code as #RRGGBB.', 'theme-tweaker');
        $help0[] = __('Launch the new color scheme in the "preview mode" by checking the preview box. (Preview means only admins can see the changes.)', 'theme-tweaker');
        $help0[] = __('Or choose to "Activate" the new scheme (for everybody) by checking that box.', 'theme-tweaker');
        $help0[] = __('Once ready, click on the "Save Changes" button to save the changes. Note that you will see the changes (in preview or activate mode) only after saving.', 'theme-tweaker');
        $help0[] = __('<b>Theme Tweaker</b> will remember your saved color schemes for any number of themes.', 'theme-tweaker');

        $o = new EzHelpTag('help1');
        $o->title = __('Click for help', 'theme-tweaker');
        $o->tipTitle = __('How to Save Stylefiles', 'theme-tweaker');
        $o->desc = __('Generating theme files and child themes.', 'theme-tweaker');
        $helpTags[] = $o;
        $help1 = array();
        $help1[] = __('You can download the tweaked theme style sheet by clicking on the "Download Stylesheet" button. It saves the changes and then downloads a style.css file that you can upload to your blog server theme directory if you want to make your changes permanent.', 'theme-tweaker');
        $help1[] = __('Or, you can click on the "Generate Child Theme" button to download a child theme stylesheet (style.css) with the colors as specified above to your local computer, which you can upload to your blog server to make them permanent. Child theme is a better way to go, because it allows you to keep the original theme files untouched.', 'theme-tweaker');
        ?>
        <h3>Instructions</h3>
        <table>
          <tr style="vertical-align:top">
            <td style="width:400px">
              <ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >
                <?php
                foreach ($helpTags as $help) {
                  echo "<li>";
                  $help->render();
                  echo "</li>\n";
                }
                ?>
              </ul>
              <div id="help0" style="display:none;">
                <ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >
                  <?php
                  foreach ($help0 as $h) {
                    echo "<li>$h</li>";
                  }
                  ?>
                </ul>
              </div>
              <div id="help1" style="display:none;">
                <ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >
                  <?php
                  foreach ($help1 as $h) {
                    echo "<li>$h</li>";
                  }
                  ?>
                </ul>
              </div>
            </td>
            <?php
            include ($this->plgDir . '/head-text.php');
            ?>
            <td>
            </td>
          </tr>
        </table>

        <form method="post" action="#">
          <?php
          if (!$this->options['kill_rating']) {
            $ez->renderRating();
          }
          if (!$this->options['kill_invites']) {
            $ez->renderInvite();
          }
          ?>
          <h3>Tweak the Colors Found in "<?php echo $mThemeName; ?>"</h3>
          <table>
            <tr style="vertical-align:top">
              <td>
                <table>
                  <tr style="vertical-align:middle;text-align:center;">
                    <td style="width:350px">
      <?php echo $this->makeTable($colors0, $colors1) ?>
                    </td>
                    <td style="width:350px">
      <?php echo $this->makeButtons($colors0, $colors1) ?>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
          <h3>Options for the Tweaked "<?php echo $mThemeName; ?>"</h3>
          <?php
          foreach ($this->ezOptions as $o) {
            $o->render();
          }
          // make the strings to save to file, just in case the user wants them
          $_SESSION['strCSS'] = $this->makeString('CSS');
          $_SESSION['strChild'] = $this->makeString('child');
          if (isset($_POST['childName'])) {
            $childName = trim($_POST['childName']);
          }
          if (empty($childName)) {
            $childName = $mThemeName . '-child';
          }
          $childDir = preg_replace('/\s+/', '-', $childName);
          $_SESSION['childName'] = $childName;
          $_SESSION['childDir'] = $childDir;
          $mThemeRoot = addslashes(get_theme_root());

          // status messages
          $statUpdate = htmlspecialchars(__('Updated Settings', "theme-tweaker"));
          $_SESSION['statUpdate'] = $statUpdate;

          $statClean = htmlspecialchars(__("Database has been cleaned. All your options for this plugin (for all themes) have been removed.", "theme-tweaker"));
          $_SESSION['statClean'] = $statClean;

          $statCSS = htmlspecialchars('Updated Settings and Generated the CSS Stylefile for your theme "' . $mThemeName . '." <br />Copy the downloaded style.css file to your blog server directory: <br />' . $mThemeRoot . '/' . $mThemeName . '<br />to make the color tweaks permanent.');

          $statChild = htmlspecialchars('Updated settings and generated a child theme stylefile for your theme "' . $mThemeName . '" with the name "' . $childName . '."<br />On your blog server, create a directory for the child theme. Directory to create is:<br />' . $mThemeRoot . '/' . $childDir . '<br />and copy the downloaded style.css file there to install the new child theme.');

          echo
          '<script type="text/javascript">
function setStatus(status){
document.getElementById(\'status\').innerHTML = status;
}
</script>';
          ?>

          <div class="submit">
            Save your color tweaks and options?<br /><br />

            <?php
            $preview = new EzSubmit('previewNow');
            $preview->desc = __('Preview', 'theme-tweaker');
            $preview->title = __('Check the preview option, save your options and <em>then</em> click here to see your color scheme', 'theme-tweaker');
            $siteurl = get_option('siteurl');
            $preview->onclick = "window.open('$siteurl', 'previewNow', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=' + 0.9 * screen.width + 'px,height=' + 0.8 * screen.height + 'px,left=' + 0.05 * screen.width + ',top=' + 0.05 * screen.width).focus();";
            $preview->tipTitle = $preview->desc;

            $update = new EzSubmit('update_themeTweakerSettings');
            $update->desc = __('Save Changes', 'theme-tweaker');
            $update->title = __('Save the changes as specified above', 'theme-tweaker');
            $update->tipTitle = $update->desc;

            $cleanDB = new EzSubmit('clean_db');
            $cleanDB->desc = __('Clean Database', 'theme-tweaker');
            $cleanDB->title = __('The <b>Database Cleanup</b> button discards all your AdSense settings you have saved so far for <b>all</b> the themes, including the current one. Use it only if you know that you will not be using these themes. Please be careful with all database operations -- keep a backup.', 'theme-tweaker');
            $cleanDB->tipWarning = true;

            $preview->render();
            $update->render();
            $cleanDB->render();
            ?>
          </div>
        </form>

        <?php
        $ez->renderWhyPro();
        ?>

        <form method='post'>
          <?php
          $this->ezTran->renderTranslator();
          ?>
        </form><br />
        <div style="background-color:#fcf;padding:5px;border: solid 1px;margin:5px;">
          <?php
          $ez->renderSupport();
          ?>
        </div>
        <?php
        include ($this->plgDir . '/tail-text.php');
        ?>
        <h3>Credits</h3>
        <table style="width:100%">
          <tr><td>
              <ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >
                <li>
                  <b>Theme Tweaker</b> uses the excellent Javascript color picker by <a href="http://jscolor.com" target="_blank" title="Javascript color picker"> JScolor</a>.
                </li>
                <li>
                  It also uses the excellent Javascript/DHTML tooltips by <a href="http://www.walterzorn.com" target="_blank" title="Javascript, DTML Tooltips"> Walter Zorn</a>.
                </li>
              </ul>
            </td>
          </tr>
        </table>
      </div>
      <?php
    }

    function cleanDB($prefix) {
      global $wpdb;
      $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '$prefix%'");
    }

    function plugin_action($links, $file) {
      if ($file == plugin_basename($this->plgDir . '/theme-tweaker.php')) {
        $settings_link = "<a href='themes.php?page=theme-tweaker.php'>" .
                __('Settings', 'theme-tweaker') . "</a>";
        array_unshift($links, $settings_link);
      }
      return $links;
    }

    function footer_action() {
      // $mThemeName = get_option('stylesheet');
      // $mFooter = $mThemeName . '_Footer';
      $mFooter = 'footer_link';
      $this->options = $this->getAdminOptions();
      $footer = $this->options[$mFooter];
      if ($footer) {
        return;
      }
      $unreal = '<div style="margin-right:auto;margin-left:auto;text-align:center;">' .
              '<font size="-3">' .
              '<a href="http://www.thulasidas.com/plugins/theme-tweaker/" ' .
              'target="_blank" title="The simplest way to tweak your theme colors!"> ' .
              'Theme Tweaker</a> by <a href="http://www.Thulasidas.com/" ' .
              'target="_blank" title="Unreal Blog proudly brings you Theme Tweaker">' .
              'Unreal</a></font></div>';
      echo $unreal;
    }

    function head_action() {
      // if it is a theme preview (from theme switcher), don't insert my style string
      if (isset($_GET['preview'])) {
        return;
      }
      $mThemeName = get_option('stylesheet');
      $mTrmKey = $mThemeName . '_Trim';
      $mPreKey = $mThemeName . '_Preview';
      $mActKey = $mThemeName . '_Activate';
      $this->options = $this->getAdminOptions();
      $stylestr = $this->options[$mTrmKey];
      if (!empty($this->options[$mActKey]) ||
              (!empty($this->options[$mPreKey]) && current_user_can('switch_themes'))) {
        echo "\n" . '<!-- Theme Tweaker (start) -->' . "\n" .
        '<style type="text/css">' . "\n" .
        $stylestr .
        "</style>\n<!-- Theme Tweaker (end) -->\n\n";
      }
    }

    function makeString($type) {
      $this->options = $this->getAdminOptions();
      $mThemeName = get_option('stylesheet');
      $mCSSKey = $mThemeName . '_Tweaked';
      $mTrmKey = $mThemeName . '_Trim';
      $stylestr = $this->options[$mCSSKey];
      ob_start();
      if ($type == 'CSS') {
        return htmlspecialchars($stylestr);
      }
    }

  }

}

if (class_exists("ThemeTweaker")) {
  $thmTwk = new ThemeTweaker();
  if (isset($thmTwk)) {
    //Initialize the admin panel
    if (!function_exists("themeTwk_ap")) {

      function themeTwk_ap() {
        global $thmTwk;
        if (function_exists('add_theme_page')) {
          $mName = 'Theme Tweaker';
          add_theme_page($mName, $mName, 'activate_plugins', basename(__FILE__), array($thmTwk, 'printAdminPage'));
        }
      }

    }

    add_action('admin_menu', 'themeTwk_ap');
    add_action('activate_' . basename($thmTwk->plgDir) . '/' . basename(__FILE__), array($thmTwk, 'init'));
    add_filter('plugin_action_links', array($thmTwk, 'plugin_action'), -10, 2);
    add_action('wp_head', array($thmTwk, 'head_action'), 99);
    add_action('wp_footer', array($thmTwk, 'footer_action'));
    add_action('init', array($thmTwk, 'session_start'));
  }
}
