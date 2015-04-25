<?php

/*
  Copyright (C) 2008 www.ads-ez.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or (at
  your option) any later version.

  This program is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!class_exists('EzAdmin')) {

  class EzAdmin {

    var $plgURL, $plgFile, $plg, $slug, $name;
    var $killAuthor = false;
    var $cdn;
    static $premia = array(array('key' => 'ads-ez',
            'name' => 'Ads EZ',
            'desc' => 'A Personal Ad Server'),
        array('key' => 'ezpaypal',
            'name' => 'EZ PayPal',
            'desc' => 'Your own e-shop for Digital Goods'),
        array('key' => 'easy-adsense',
            'name' => 'Easy AdSense Ultra',
            'desc' => 'Bootstrap/AJAX version of Easy AdSense'),
        array('key' => 'google-adsense',
            'name' => 'Google AdSense Ultra',
            'desc' => 'The Ultimate AdSense plugin - Combines Easy AdSense, AdSense Now! and Google AdSense in one sleek, modern interface.'));

    function __construct($plg, $slug, $plgURL) {
      $this->plg = $plg;
      $this->slug = $slug;
      $this->plgURL = $plgURL;
      $this->cdn = $plgURL . 'img';
    }

    function __destruct() {

    }

    function EzAdmin($plg, $slug, $plgURL) {
      if (version_compare(PHP_VERSION, "5.0.0", "<")) {
        $this->__construct($plg, $slug, $plgURL);
        register_shutdown_function(array($this, "__destruct"));
      }
    }

    function renderNags(&$options) {
      if (empty($options['kill_rating'])) {
        $this->renderRating();
      }
      else {
        echo "<input type='hidden' name='kill_rating' value='true' />";
      }
      if (empty($options['kill_invites'])) {
        $this->renderInvite();
      }
      else {
        echo "<input type='hidden' name='kill_invites' value='true' />";
      }
    }

    function renderInvite() {
      if ($this->killAuthor) {
        return;
      }
      $plg = $this->plg;
      $slug = $this->slug;
      $plgLongName = $plg['value'];
      $plgPrice = $plg['price'];
      $yesTip = sprintf(__('Buy %s Pro for $%s. PayPal payment. Instant download.', 'easy-common'), $plgLongName, $plgPrice);
      $yesTitle = __('Get the Pro version now!', 'easy-common');
      $noTip = __('Continue using the Lite version, and hide this message. After clicking this button, please remember to save your options to hide this box for good.', 'easy-common');
      $noTitle = __('Stay Lite', 'easy-common');
      $hideTip = __('Click the link to hide this box. After clicking this link, please remember to save your options to hide this box for good.', 'easy-common');
      if (empty($plg['benefits'])) {
        return;
      }
      $benefits = $plg['benefits'];

      $s1 = __("Want More Features?", 'easy-common');
      $s2 = __("The Pro version of this plugin gives you more features and benefits.", 'easy-common');
      $s3 = __("For instance", 'easy-common');
      $s4 = __("And much more.", 'easy-common');
      $s5 = __("New features and bug fixes will first appear in the Pro version before being ported to this freely distributed Lite edition.", 'easy-common');
      $s6 = __("Go Pro!", 'easy-common');
      $s7 = __("No thanks", 'easy-common');
      $s8 = __("Do not show this anymore", 'easy-common');
      $s9 = sprintf(__("Thank you for using %s!", 'easy-common'), $plgLongName);
      $s10 = __("Please Save Options to hide this box forever", 'easy-common');

      echo <<<ENDINVITE
<input type="hidden" id="kill_invites" name="kill_invites" value="" />
<div class="updated" id="tnc">
<p><h3>$s1 <a href="#" onmouseover="Tip('$yesTip', WIDTH, 200, CLICKCLOSE, true, TITLE, '$yesTitle')" onmouseout="UnTip()" onclick = "buttonwhich('Yes')">Go Pro!</a></h3>
$s2 $s3,
<ol>
$benefits
</ol>
$s4 $s5<br />
<input onmouseover="Tip('$yesTip', WIDTH, 200, CLICKCLOSE, true, TITLE, '$yesTitle')" onmouseout="UnTip()" type = "button" id = "ybutton" value = "$s6" onclick = "buttonwhich('Yes')" />
<input onmouseover="Tip('$noTip', WIDTH, 200, CLICKCLOSE, true, TITLE, '$noTitle')" onmouseout="UnTip()" type = "button" id = "nbutton" value = "$s7" onclick = "buttonwhich('No')" />
<small style='font-weight:normal;'><a id='hideInvite' href='#' style='float:right; display:block; border:none;'  onmouseover="Tip('$hideTip', WIDTH, 200, CLOSEBTN, true, CLICKCLOSE, true, FIX, [this, 5, 5], TITLE, 'Hide this Box')" onclick = "buttonwhich('No')">
$s8</a></small>
<script type = "text/javascript">
function hideInvite() {
  document.getElementById("tnc").style.display = 'none';
}
function buttonwhich(message) {
  document.getElementById("ybutton").style.display = 'none';
  document.getElementById("nbutton").disabled = 'true';
  document.getElementById("kill_invites").value = 'true' ;
  setTimeout('hideInvite()', 5000);
  if (message == 'Yes') popupwindow('http://buy.thulasidas.com/$slug', '$s6', 1024, 768) ;
  if (message == 'No') {
    document.getElementById("nbutton").value = '$s9 $s10';
    document.getElementById("hideInvite").innerHTML = '$s10';
  }
}
</script>
</div>
ENDINVITE;
    }

    function getPlgKey($slug = '') {
      if (empty($slug)) {
        $plgKey = basename($this->plgFile, '.php');
      }
      else {
        $plgKey = $slug;
      }
      $plgKey = str_replace(array('-lite', '-pro'), "", $plgKey);
      switch ($plgKey) {
        case 'easy-latex':
          $plgKey = 'easy-wp-latex-lite';
          break;
        case 'ezpaypal':
        case 'easy-paypal':
        case 'easy-paypal-lite':
          $plgKey = 'easy-paypal-lte';
          break;
        case 'ads-ez':
        case 'easy-quiz':
        case 'easy-text-links':
        case 'a-function-hitman':
          break;
        default:
          $plgKey .= '-lite';
      }
      return $plgKey;
    }

    function renderRating($killable = true) {
      if ($this->killAuthor) {
        return;
      }
      $plgFile = $this->plgFile;
      $plg = $this->plg;
      $plgCTime = filemtime($plgFile);
      $plgLongName = $plg['value'];
      $plgKey = $this->getPlgKey();
      $hideTip = htmlspecialchars(__('Click the link to hide this box. After clicking this link, please remember to save your options to hide this box for good.', 'easy-common'));

      if (time() > $plgCTime + (60 * 60 * 24 * 30)) {
        $msg = __("You've installed this plugin over a month ago.", 'easy-common');
      }
      else {
        $msg = __("You will find it feature-rich and robust.", 'easy-common');
      }
      $display = '';
      if (!$killable) {
        $display = "style='display:none'";
      }

      $s1 = __("If you are satisfied with how well it works, why not rate it and recommend it to others?", 'easy-common');
      $s2 = __("Click here", 'easy-common');
      $s3 = __("Do not show this anymore", 'easy-common');
      $s4 = __("Please Save Options to hide this box forever", 'easy-common');

      echo <<<ENDRATING
<div class='updated' id='rating'>
<p>Thanks for using <i><b>$plgLongName</b></i>! $msg <br />
$s1 <a href='http://wordpress.org/extend/plugins/$plgKey/' class='popup' data-height='1024'>$s2</a>
<small style='font-weight:normal;'><a id='hideRating' $display href='#' style='float:right; display:block; border:none;'  onmouseover="Tip('$hideTip', WIDTH, 200, CLOSEBTN, true, CLICKCLOSE, true, FIX, [this, 5, 5], TITLE, 'Hide this Box')" onclick = "hideme()">
$s3</a></small></p></div>
<input type="hidden" id="kill_rating" name="kill_rating" value="" />
<script type = "text/javascript">
function hideRating() {
  document.getElementById("rating").style.display = 'none';
}
function hideme() {
  document.getElementById("kill_rating").value = 'true' ;
  document.getElementById("hideRating").innerHTML = '$s4';
  setTimeout('hideRating()', 4000);
}
</script>
ENDRATING;
    }

    function renderHeadText($isPro = false) {
      $plg = $this->plg;
      $slug = $this->slug;
      $value = '<em><strong>' . $plg['value'] . '</strong></em>';
      $price = $plg['price'];
      $onclick = "class='popup'";
      $s1 = sprintf(__('Download the Lite version of %s', 'easy-common'), $value);
      $s2 = __('Lite Version', 'easy-common');
      $s3 = sprintf(__('Buy the Pro version of %s for $%.2f', 'easy-common'), $plg['value'], $price);
      $s4 = __('Instant download link.', 'easy-common');
      $s5 = __('Pro Version', 'easy-common');
      $why = addslashes($plg['pro']);
      if ($isPro) {
        $moreInfo = "&nbsp; <a href='http://buy.thulasidas.com/lite/$slug.zip' title='$s1'>$s2 </a>&nbsp; <a href='http://buy.thulasidas.com/$slug' title='$s3. $s4' $onclick>$s5</a>";
        $version = 'Pro';
      }
      else {
        $moreInfo = "$s2 and <b><a href='http://buy.thulasidas.com/$slug' title='$s3. $s4' $onclick>$s5</a></b>";
        $version = 'Lite';
      }

      $s6 = sprintf(__('You are using the %s version of %s, which is available in two versions:', 'easy-common'), $version, $value);
      $s7 = sprintf(__('And it costs only $%.2f!', 'easy-common'), $price);
      $s8 = __('Get the Pro version now!', 'easy-common');

      echo "<b>$s8</b>
<a href='http://buy.thulasidas.com/$slug' title='$s3. $s4' $onclick><img src='{$this->cdn}/ezpaypal.png' alt='ezPayPal' class='alignright'/></a>
<br />
$s6
<ul><li>
$moreInfo
</li>
<li>$why $s7</li>
</ul>";
    }

    function renderProText($isPro = false) {
      echo "<div id='pro' style='display:none'>";
      $this->renderHeadText($isPro);
      echo "</div>";
      if ($this->killAuthor) {
        return;
      }
      $plg = $this->plg;
      $slug = $this->slug;
      $value = '<em><strong>' . $plg['value'] . '</strong></em>';
      $filter = '';
      if (stripos($slug, 'adsense') !== FALSE) {
        $filter = __("e.g., a filter to ensure AdSense policy compliance.", 'easy-common');
      }
      $price = $plg['price'];
      $onclick = "class='popup'";

      $s3 = sprintf(__('Buy the Pro version of %s for $%.2f', 'easy-common'), $plg['value'], $price);
      $s4 = __('Pro Version', 'easy-common');
      $s5 = __('Buy the Pro Version', 'easy-common');

      echo "<div style='background-color:#ffcccc;padding:5px;border:solid 1px;height:115px;overflow-y:auto;margin:0' onmouseover=\"TagToTip('pro', WIDTH, 300, TITLE, '$s5',STICKY, 1, CLOSEBTN, true, CLICKCLOSE, true, FIX, [this, -30, 5])\"><div style='font-size:14px;color:#a48;font-variant: small-caps;text-decoration:underline;text-align:center;'><b>$s4</b></div>";

      $s8 = sprintf(__('It costs only $%.2f!', 'easy-common'), $price);
      $s9 = __('Instant download link.', 'easy-common');
      if ($isPro) {
        $value .= '<b><i> Pro</i></b>';
        $s6 = sprintf(__("You are enjoying $value with \"Pro\" features.", 'easy-common'), $value);
        $s7 = __("Please consider buying it, if you haven't already paid for it.", 'easy-common');
        echo "$s6 $s7 <a href='http://buy.thulasidas.com/$slug' title='$s3. $s9' $onclick>$s8</a>";
      }
      else {
        $value .= '<b><i> Lite</i></b>';
        $s10 = sprintf(__('Thank you for using %s. The "Pro" version gives you more options.', 'easy-common'), $value);
        $s11 = __("Consider buying it.", 'easy-common');
        echo "$s10 $filter $s11 <a href='http://buy.thulasidas.com/$slug' title='$s3. $s9' $onclick>$s8</a>";
      }
      echo "</div>";
    }

    function renderPremiumText($slug) {
      if ($this->killAuthor) {
        return;
      }
      require(dirname(__FILE__) . '/myPlugins.php');

      $plg = $this->myPlugins[$slug];

      echo "<div id='premium-$slug' style='display:none'><a href='http://www.thulasidas.com/$slug' target='_blank' class='popup' data-height='1024'><img src='{$this->cdn}/plg-$slug.jpg' style='border: solid 1px;vertical-align:bottom;max-width:100%' alt='Another Premium Plugin the this Author' /></a><br><br>";
      $value = '<em><strong>' . $plg['value'] . '</strong></em>';
      $toolTip = $plg['title'];
      $price = $plg['price'];
      $onclick = "class='popup'";

      $s2 = __('Lite Version', 'easy-common');
      $s3 = sprintf(__('Buy the Pro version of %s for $%.2f', 'easy-common'), $plg['value'], $price);
      $s4 = __('Instant download link.', 'easy-common');
      $s5 = __('Pro Version', 'easy-common');
      $plgKey = $this->getPlgKey($slug);
      $moreInfo = "<a href='http://wordpress.org/extend/plugins/$plgKey/' class='popup' data-height='1024'>$s2</a> and <b><a href='http://buy.thulasidas.com/$slug' title='$s3. $s4' $onclick>$s5</a></b>";
      $toolTip .= addslashes('<br />' . $moreInfo);
      $why = addslashes($plg['pro']);

      $s6 = sprintf(__('%s is available in two versions:', 'easy-common'), $value);
      $s7 = sprintf(__('And it costs only $%.2f!', 'easy-common'), $price);
      $s8 = __('Get the Pro version now!', 'easy-common');

      echo "{$plg['blurb']} {$plg['desc']}
<br>
<br>
<b style='color:red;font-size:1.1em'>$s8</b>
<a href='http://buy.thulasidas.com/$slug' title='$s3. $s4' $onclick><img src='{$this->cdn}/ezpaypal.png' alt='ezPayPal' class='alignright'/></a>
$s6
<ul><li>
$moreInfo
</li>
<li>$why $s7</li>
</ul></div>";
    }

    function renderAffiliate() {
      if ($this->killAuthor) {
        return;
      }

      $roll = rand(0, 4);
      if ($roll > 3) {
        $select = rand(0, 4);
        echo "<div style='padding:0px;border:none;text-align:center' id='support' ><a href='http://www.thulasidas.com/professional-php-services/' target='_blank' onmouseover=\"TagToTip('proservices', WIDTH, 295, TITLE, 'Professional Services', FIX, [this, -65, 110], CLICKCLOSE, true, CLOSEBTN, true)\"><img src='{$this->cdn}/300x250-0$select.jpg' border='0' style='vertical-align:bottom;max-width:150px' alt='Professional Services from the Plugin Author' /></a></div>";
      }
      else {
        extract(self::$premia[$roll]);
        echo "<div style='padding:0px;border:none;text-align:center' id='premium'><a href='http://www.thulasidas.com/$key' target='_blank' class='popup' data-height='1024' onmouseover=\"TagToTip('premium-$key', WIDTH, 295, TITLE, '$desc', FIX, [this, -65, -30], CLICKCLOSE, true, CLOSEBTN, true)\" ><img src='{$this->cdn}/plg-$key.jpg' style='border: solid 1px;vertical-align:bottom;max-width:150px' alt='$name - Another Premium Plugin the this Author' /></a></div>";
      }
    }

    function renderTipDivs() {
      echo <<<ENDDIVS
<span id="proservices" style='display:none;'>
The author of this plugin may be able to help you with your WordPress or plugin customization needs and other PHP related development. <a href='http://www.thulasidas.com/professional-php-services/' target='_blank'>Contact me</a> if you find a plugin that almost, but not quite, does what you are looking for, or if you need any other professional services. If you would like to see my credentials, take a look at <a href='http://www.thulasidas.com/col/Manoj-CV.pdf' target='_blank'>my CV</a>.
</span>
ENDDIVS;

      foreach (self::$premia as $p) {
        $this->renderPremiumText($p['key']);
      }
    }

    static function makeTextWithTooltip($text, $tip, $title = '', $width = '') {
      if (!empty($title)) {
        $titleText = "TITLE, '$title',STICKY, 1, CLOSEBTN, true, CLICKCLOSE, true,";
      }
      if (!empty($width)) {
        $widthText = "WIDTH, $width,";
      }
      $return = "<span style='text-decoration:none' " .
              "onmouseover=\"Tip('" . htmlspecialchars($tip) . "', " .
              "$widthText $titleText FIX, [this, 5, 5])\" " .
              "onmouseout=\"UnTip()\">$text</span>";
      return $return;
    }

    static function makeTextWithTooltipTag($plg, $text, $tip, $title = '', $width = '') {
      if (!empty($title)) {
        $titleText = "TITLE, '$title',STICKY, 1, CLOSEBTN, true, CLICKCLOSE, true,";
      }
      if (!empty($width)) {
        $widthText = "WIDTH, $width,";
      }
      $return = "<span style='text-decoration:none' " .
              "onmouseover=\"TagToTip('" . $plg . "', " .
              "$widthText $titleText FIX, [this, 5, 5])\" " .
              "onmouseout=\"UnTip()\">$text</span>";
      return $return;
    }

    function renderPlg($slug, $plg) {
      if ($this->killAuthor) {
        return;
      }
      if (!empty($plg['hide']) && $plg['hide']) {
        return;
      }
      if (!empty($plg['kind']) && $plg['kind'] != 'plugin') {
        return;
      }
      $value = '<em><strong>' . $plg['value'] . '</strong></em>';
      $desc = $plg['desc'];
      $title = $plg['title'];
      $url = 'http://www.thulasidas.com/plugins/' . $slug;
      $link = '<b><a href="' . $url . '" target="_blank" class="popup" data-height="1024">' . $value . '</a></b>&nbsp; ';
      $text = $link . $desc;
      $price = $plg['price'];
      $onclick = "class='popup'";
      $moreInfo = "&nbsp;&nbsp;<a href='http://www.thulasidas.com/plugins/$slug' title='More info about $value at Unreal Blog' class='popup' data-height='1024'>More Info</a> ";
      $liteVersion = "&nbsp;&nbsp; <a href='http://buy.thulasidas.com/lite/$slug.zip' title='Download the Lite version of $value'>Get Lite Version</a> ";
      $proVersion = "&nbsp;&nbsp; <a href='http://buy.thulasidas.com/$slug' title='Buy the Pro version of $value for \$$price' $onclick>Get Pro Version</a><br />";
      $why = "<a href='http://buy.thulasidas.com/$slug' title='Buy the Pro version of the $slug plugin' $onclick><img src='{$this->cdn}/ezpaypal.png' alt='ezPayPal -- Instant PayPal Shop.' class='alignright' /></a>
<br />" . $plg['pro'];
      echo "<li>" . self::makeTextWithTooltip($text, $title, $value, 350) .
      "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
      self::makeTextWithTooltip($moreInfo, "Read more about $value at its own page.<br />" . $title, "More Information about $value", 300) .
      self::makeTextWithTooltip($liteVersion, $title, "Download $value - the Lite version", 300) .
      self::makeTextWithTooltipTag($slug, $proVersion, $why, "Get $value Pro!", 300) .
      "<span id=$slug> $why </span>" .
      "</li>\n";
    }

    function renderBook($slug, $plg) {
      if ($this->killAuthor) {
        return;
      }
      if (empty($plg['kind']) || $plg['kind'] != 'book') {
        return;
      }
      $value = '<em><strong>' . $plg['value'] . '</strong></em>';
      $desc = $plg['desc'];
      $title = $plg['title'];
      $url = $plg['url'];
      $link = "<b><a href='$url' title='More info about $value' target=_blank class='popup' data-height='1024'>$value</a></b>&nbsp; ";
      $text = $link . $desc;
      $price = $plg['price'];
      $onclick = "class='popup'";
      $moreInfo = "&nbsp;&nbsp; <a href='$url' title='More info about $value' target=_blank class='popup' data-height='1024'>More Info</a> ";
      $amazon = $plg['amazon'];
      if (!empty($amazon)) {
        $buyAmazon = "&nbsp;&nbsp; <a href='$amazon' title='Get $value from Amazon.com' target=_blank class='popup' data-height='1024'>Get it at Amazon</a> ";
      }
      $buyNow = "&nbsp;&nbsp; <a href='http://buy.thulasidas.com/$slug' title='Buy and download $value for \$$price' target=_blank $onclick>Buy and Download now!</a><br />";
      $why = "<a href='http://buy.thulasidas.com/$slug' title='$slug' $onclick><img src='{$this->cdn}/ezpaypal.png' alt='ezPayPal -- Instant PayPal Shop.' class='alignright' /></a>
<br />" . $title . $desc . " $value costs only \$$price -- direct from the author.";
      echo "<li>" . self::makeTextWithTooltip($text, $title, $value, 350) .
      "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
      self::makeTextWithTooltip($moreInfo, "Read all about $value at its own site.<br />", "$value", 300) .
      self::makeTextWithTooltip($buyAmazon, $title, "Buy $value from Amazon", 350) .
      self::makeTextWithTooltipTag("$slug-book", $buyNow, $why, "Buy $value!", 300) .
      "<span id=$slug-book> $why </span>" .
      "</li>\n";
    }

    function getPlgInfo() {
      $slug = $this->slug;
      $plugins = get_plugins();
      $ret = array('Title' => '', 'Version' => '', 'Info' => '');
      $break = '';
      foreach ($plugins as $k => $p) {
        if (!is_plugin_active($k)) {
          continue;
        }
        $baseDir = dirname($k);
        $baseDirSmall = str_replace(array("-lite", "-pro"), "", $baseDir);
        if ($baseDir == $slug || $baseDirSmall == $slug) {
          $version = $p['Version'];
          if (!empty($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
          }
          else {
            $referer = 'Unknown';
          }
          $info = "$break{$p['Title']} V{$p['Version']} (Referer: $referer)";
          $ret = array('Title' => $p['Title'], 'Version' => $version, 'Info' => $info);
          return $ret;
        }
      }
      return $ret;
    }

    function renderSupport() {
      echo '<div style="background-color:#fcf;padding:5px;border: solid 1px;margin:5px;">
';
      $plg = $this->plg;
      $slug = $this->slug;
      $value = $plg['value'];
      $url = 'http://www.thulasidas.com/plugins/' . $slug . '#faq';
      $link = '<a href="' . $url . '" target="_blank" class="popup" data-height="1024">' . $value . '</a>';
      echo "&nbsp;<a href='http://support.thulasidas.com' class='popup' title='";
      _e('Ask a support question (in English or French only) via ezSupport @ $0.95', 'easy-common');
      echo "'><img src='{$this->cdn}/ezsupport.png' class='alignright' alt='ezSupport Portal'/></a>";
      printf(__("If you need help with %s, please read the FAQ section on the %s page. It may answer all your questions.", 'easy-common'), $value, $link);
      echo "<br style='line-height: 20px;'/>";
      _e("Or, if you still need help, you can raise a support ticket.", 'easy-common');
      echo "&nbsp;<a href='http://support.thulasidas.com' class='popup' title='";
      _e('Ask a support question (in English or French only) via ezSupport @ $0.95', 'easy-common');
      echo "'>";
      _e("[Request Paid Support]", 'easy-common');
      $info = $this->getPlgInfo();
      echo "</a>&nbsp;<small><em>[";
      _e('Using our ezSupport Ticket System.', 'easy-common');
      echo "]</em></small>";
      echo "<small style='float:right'><em>[";
      printf(__('You are using %s (V%s)', 'easy-common'), $value, $info['Version']);
      echo "]</em></small>";
      $_SESSION['ezSupport'] = $info['Info'];
      echo "</div>";
    }

    function renderWhyPro($short = false) {
      if ($this->killAuthor && !$short) {
        return;
      }
      $plg = $this->plg;
      $slug = $this->slug;
      $value = $plg['value'];
      $toolTip = $plg['title'];
      $price = $plg['price'];
      $buyURL = "http://buy.thulasidas.com/$slug";
      $infoURL = "http://www.thulasidas.com/plugins/$slug";
      $s1 = sprintf(__('Get %s', 'easy-common'), $value);
      $s2 = sprintf(__('More info about %s', 'easy-common'), $value);
      $s3 = sprintf(__('Buy the Pro version of %s for $%.2f', 'easy-common'), $value, $price);
      $s4 = __('More Info', 'easy-common');

      $onclick = "class='popup'";
      $moreInfo = " &nbsp;  &nbsp; <a href='$infoURL' title='$s2' target=_blank class='popup' data-height='1024'> $s4 </a>&nbsp; <a href='$buyURL' $onclick title='$s3'>Get Pro Version</a>";
      $toolTip .= addslashes('<br />' . $moreInfo);
      $why = addslashes($plg['pro']);
      echo '<div style="background-color:#cff;padding:5px;border: solid 1px;margin:5px;padding-bottom:15px;">';
      if ($short) {
        $s5 = __('Buy the Pro Version', 'easy-common');
        $s6 = __('More features, more power!', 'easy-common');

        echo "<span onmouseover=\"TagToTip('pro', WIDTH, 350, TITLE, 'Buy the Pro Version',STICKY, 1, CLOSEBTN, true, CLICKCLOSE, true, FIX, [this, 5, 5])\"><b><a href='$buyURL' $onclick target='_blank'>$s5</a></b>&nbsp; $s6</span>";
      }
      else {
        $s7 = sprintf(__('You are using the Lite version of %s, which is available in two versions: <b>Lite</b> and <b>Pro</b>.', 'easy-common'), $value);
        echo "<b>Get Pro Version!</b>
<a href='http://buy.thulasidas.com/$slug' title='$s3' $onclick><img src='{$this->cdn}/ezpaypal.png' alt='ezPayPal' class='alignright' /></a>
<br />
$s7
<ul><li>
$moreInfo
<li>$why</li>
</ul>";
      }
      echo '</div>';
    }

  }

}