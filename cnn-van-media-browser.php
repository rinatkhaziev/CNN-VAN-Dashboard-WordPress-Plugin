<?php
/* CNN VAN Dashboard Wordpress Plugin
  Copyright (C) 2014 thePlatform for Media Inc.

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along
  with this program; if not, write to the Free Software Foundation, Inc.,
  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA. */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>

        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="tp:EnableExternalController" content="true" />
        <?php
        /*
         * Load scripts and styles
         */
        wp_print_scripts( array( 'van_mediaview_js' ) );
        wp_print_styles( array( 'bootstrap_van_css', 'van_media_browser_css' ) );
        ?>
        <script type="text/javascript">
            var vanParams = {};
        </script>

    </head>
    <body>
        <div class="van">
            <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
                <div class="row">
                    <div class="navbar-header">
                        <a class="navbar-brand" href="#">VAN Dashboard</a>
                    </div>
                    <form class="navbar-form navbar-left" role="search" onsubmit="return false;"><!--TODO: Add seach functionality on Enter -->
                        <div class="form-group">
                            <input id="input-search" type="text" class="form-control" placeholder="Keywords">
                        </div>
                        <button id="btn-feed-preview" type="button" class="btn btn-default">Search</button>
                    </form>
                    <p class="navbar-text bar-label-text">Sort:</p>
                    <form class="navbar-form navbar-left sort-bar-nav" role="sort">
                        <select id="selectpick-sort" class="form-control">
                            <option>Published&nbsp;</option>
                            <!--option>Added</option-->
                            <option>Title</option>
                            <option>Updated</option>
                        </select>
                    </form>
                    <p class="navbar-text bar-label-text">Filter:</p>
                    <form class="navbar-form navbar-left sort-bar-nav" role="filter">
                        <select id="date-filter" class="form-control">
                            <option>No Filter</option>
                            <option>Past Hour</option>
                            <option>Past 24 Hours</option>
                            <option>Past 48 Hours</option>
                            <option>Past 7 Days</option>
                        </select>
                    </form>
                    <div id="my-content" class="navbar-left">
                        <p class="navbar-text sort-bar-text"><input type="checkbox"> My Content</p>
                    </div>
                    <div id="raw-content" class="navbar-left">
                        <p class="navbar-text sort-bar-text"><input type="checkbox"> Include Raw/File</p>
                    </div>
                    <div id="load-overlay-container">
                        <img id="load-overlay" src="<?php echo plugins_url( '/images/loading.gif', __FILE__ ) ?>" class="loadimg navbar-right">
                    </div>
                </div>

            </nav>

            <div class="fs-main">
                <div id="filter-container">
                    <div id="filter-affix" class="scrollable affix-top">
                        <div id="list-categories" class="list-group">
                            <a class="list-group-item active">
                                Categories
                            </a>
                            <a href="#" class="list-group-item cat-list-selector">All Videos</a>
                        </div>


                    </div>
                </div>

                <div id="content-container">
                    <div id="message-panel"></div>
                    <div id="media-list"></div>
                </div>
                <div id="info-container">
                    <div id="info-affix" class="scrollable affix-top">
                        <div id="info-player-container">
                            <div id="modal-player" class="marketplacePlayer">
                                <iframe id="player" width="320px" height="180px" frameBorder="0" seamless="seamless" src="http://player.theplatform.com/p/van-dev/cHE28glAlb_M/embed/"
                                        webkitallowfullscreen mozallowfullscreen msallowfullscreen allowfullscreen></iframe>
                            </div>
                            <br>
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs">
                                <li id="metadata-tab" class="active"><a href="#metadata" data-toggle="tab">Metadata</a></li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div class="tab-pane active" id="metadata">
                                    <div id="panel-contentpane" class="panel panel-default">
                                        <!--div class="panel-heading">
                                            <strong>Metadata</strong>
                                        </div-->
                                        <div class="panel-body">
                                            <div class="row">
                                                <strong>Title:</strong>
                                                <span id="media-title"></span>
                                            </div>
                                            <div class="row">
                                                <strong>Description:</strong>
                                                <span id="media-description"></span>
                                            </div>
                                            <div class="row">
                                                <strong>Video Runtime:</strong>
                                                <span id="media-video-duration"></span>
                                            </div>
                                            <div class="row">
                                                <strong>Categories:</strong>
                                                <span id="media-categories"></span>
                                            </div>
                                            <div class="row">
                                                <strong>Addl. Categories:</strong>
                                                <span id="media-addl-categories"></span>
                                            </div>
                                            <div class="row">
                                                <strong>Keywords:</strong>
                                                <span id="media-keywords"></span>
                                            </div>
                                            <div class="row">
                                                <strong>Source:</strong>
                                                <span id="media-provider"></span>
                                            </div>
                                            <div class="row">
                                                <strong>Embargoes:</strong>
                                                <span id="media-embargoes"></span>
                                            </div>
                                            <div class="row">
                                                <strong>Publish Date:</strong>
                                                <span id="media-pubdate"></span>
                                            </div>
                                            <div class="row">
                                                <strong>Updated Date:</strong>
                                                <span id="media-updated"></span>
                                            </div>
                                            <div class="row">
                                                <strong>Expiration Date:</strong>
                                                <span id="media-expiration"></span>
                                            </div>
                                            <div class="row">
                                                <strong>Video ID:</strong>
                                                <span id="media-video-id"></span>
                                            </div>
                                            <div class="row">
                                                <strong>Thumbnail:</strong>
                                                <span id="media-thumbnail"></span>
                                            </div>
                                        </div>
                                        <button type="button" id="btn-embed" class="btn btn-primary btn-xs">Embed</button>
                                        <button type="button" id="btn-embed-close" class="btn btn-primary btn-xs">Embed and close</button>
                                        <button type="button" id="btn-set-image" class="btn btn-primary btn-xs">Set Featured Image</button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>