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

if (!$ez->killAuthor) {
  ?>
  <table class="form-table" >
    <tr>
      <td>
        <ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >
          <li>
            <?php _e('Check out my other plugin and PHP efforts:', 'easy-common'); ?>
            <ul style="margin-left:0px; padding-left:30px;list-style-type:square; list-style-position:inside;" >

              <?php
              $myPluginsU = array_unique($this->myPlugins, SORT_REGULAR);
              unset($myPluginsU[$ez->slug]);
              foreach ($myPluginsU as $k => $p) {
                if (isset($p['hide']) || isset($p['kind'])) {
                  unset($myPluginsU[$k]);
                }
              }
              $keys = array_rand($myPluginsU, 3);
              foreach ($keys as $name) {
                if ($name != $ez->slug) {
                  $ez->renderPlg($name, $myPluginsU[$name]);
                }
              }
              ?>

            </ul>
          </li>

          <li>
            <?php _e('My Books -- on Physics, Philosophy, making Money etc:', 'easy-common'); ?>

            <ul style="margin-left:0px; padding-left:30px;list-style-type:square; list-style-position:inside;" >

              <?php
              foreach ($this->myPlugins as $name => $plg) {
                $ez->renderBook($name, $plg);
              }
              ?>

            </ul>
          </li>

        </ul>

      </td>
    </tr>
  </table>
  <script type = "text/javascript">
    function popupwindow(url, title, w, h) {
      return ezPopUp(url, title, w, h);
    }
    function ezPopUp(url, title, w, h) {
      var wLeft = window.screenLeft ? window.screenLeft : window.screenX;
      var wTop = window.screenTop ? window.screenTop : window.screenY;
      var left = wLeft + (window.innerWidth / 2) - (w / 2);
      var top = wTop + (window.innerHeight / 2) - (h / 2);
      window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
      return true;
    }
    jQuery(document).ready(function () {
      jQuery('body').on('click', ".popup", function (e) {
        e.preventDefault();
        var url = jQuery(this).attr('href');
        var title = "Window";
        var w = 1024;
        var h = 728;
        if (jQuery(this).attr('data-height')) {
          h = jQuery(this).attr('data-height');
          w = 1000;
        }
        return ezPopUp(url, title, w, h);
      });
    });
  </script>
  <?php
}
