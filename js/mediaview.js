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

jQuery(document).ready(function () {
    //Parse params and basic setup.
    var queryParams = vanHelper.getParameters();
    vanParams.selectedCategory = '';
    vanParams.feedEndRange = 0;
    vanParams.queryString = ''
    vanParams.feedQuery = ''
    if (van_wp_data.feed_pid)
        vanParams.provider = van_wp_data.feed_pid.substring(4).toUpperCase()
    $pdk.bind("player");    
    jQuery('#load-overlay').hide();
    vanHelper.getCategoryList(buildCategoryAccordion);

    jQuery('#btn-embed').click(function() {

        var shortcode = '[van id="' + vanParams.selectedMedia+ '"]';
    
        var win = window.dialogArguments || opener || parent || top;
        var editor = win.tinyMCE.activeEditor;
        var isVisual = (typeof win.tinyMCE != "undefined") && editor && !editor.isHidden(); 
        if (isVisual) {
            editor.execCommand('mceInsertContent', false, shortcode);
        } 
        else {
            var currentContent = jQuery('#content', window.parent.document).val();
            if ( typeof currentContent == 'undefined' )
                currentContent = '';        
            jQuery( '#content', window.parent.document ).val( currentContent + shortcode );
        }        
    })

    jQuery('#btn-embed-close').click(function() {
        jQuery('#btn-embed').click();
        var win = opener || parent
        if (win.tinyMCE.majorVersion > 3)
            win.tinyMCE.activeEditor.windowManager.close();
        else
            win.jQuery('#cnn-embed-dialog').dialog('close');
    })

    jQuery('#btn-set-image').click(function() {
        var post_id = window.parent.jQuery('#post_ID').val();
        if (!vanParams.selectedThumb || ! post_id)
            return;

        var data = {
                action: 'set_thumbnail',                
                img: vanParams.selectedThumb,  
                id: post_id,          
                _wpnonce: van_wp_data.tp_nonce
        };
                        
        jQuery.post( ajaxurl, data, function(response) {
            if (response.indexOf('set-post-thumbnail') != -1)
                window.parent.jQuery('#postimagediv .inside').html(response);
        });
    })


    /**
     * Set up the infinite scrolling media list
     */
    jQuery('#media-list').infiniteScroll({
        threshold: 100,
        onEnd: function () {
            //No more results
        },
        onBottom: function (callback) {
            jQuery('#load-overlay').show(); // show loading before we call getVideos
            var theRange = parseInt(vanParams.feedEndRange);
            theRange = (theRange + 1) + '-' + (theRange + 20);
            vanHelper.getVideos(vanParams.feedQuery.appendParams({range: theRange}), function (resp) {
                if (resp['isException']) {
                    jQuery('#load-overlay').hide();
                    //what do we do on error?
                }

                vanParams.feedResultCount = resp['totalResults'];
                vanParams.feedStartRange = resp['startIndex'];
                vanParams.feedEndRange = 0;
                if (resp['entryCount'] > 0) 
                    vanParams.feedEndRange = resp['startIndex'] + resp['entryCount'] - 1;
                else
                    notifyUser('info','No Results');

                var entries = resp['entries'];
                for (var i = 0; i < entries.length; i++)
                    addMediaObject(entries[i]);

                jQuery('#load-overlay').hide();
                Holder.run();
                callback(parseInt(vanParams.feedEndRange) < parseInt(vanParams.feedResultCount)); //True if there are still more results.
            });
        }
    });

    //This is for setting a section "scrollable" so it will scroll without scrolling everything else.
    jQuery('.scrollable').on('DOMMouseScroll mousewheel', function (ev) {
        var $this = jQuery(this),
            scrollTop = this.scrollTop,
            scrollHeight = this.scrollHeight,
            height = $this.height(),
            delta = (ev.type == 'DOMMouseScroll' ? ev.originalEvent.detail * -40 : ev.originalEvent.wheelDelta),
            up = delta > 0;

        var prevent = function () {
                ev.stopPropagation();
                ev.preventDefault();
                ev.returnValue = false;
                return false;
            };

        if (!up && -delta > scrollHeight - height - scrollTop) {
            // Scrolling down, but this will take us past the bottom.
            $this.scrollTop(scrollHeight);
            return prevent();
        } else if (up && delta > scrollTop) {
            // Scrolling up, but this will take us past the top.
            $this.scrollTop(0);
            return prevent();
        }
    });

    /**
     * Search form event handlers
     */
    jQuery('#btn-feed-preview').click(refreshView);

    jQuery('input:checkbox', '#my-content').click(refreshView);

    jQuery('input:checkbox','#raw-content').click(refreshView);

    jQuery('#selectpick-sort').on('change', refreshView);

    jQuery('#input-search').keyup(function (event) {
        if (event.keyCode == 13) refreshView();
    });

    jQuery('#date-filter').on('change',refreshView);

    /**
     * Look and feel event handlers
     */    
    jQuery(document).on('click', '.media', function () {
        updateContentPane(jQuery(this).data('media'));    
        jQuery('.media').css('background-color', '');
        jQuery(this).css('background-color', '#D8E8FF');
        jQuery(this).data('bgc', '#D8E8FF');
        vanParams.currentRelease = jQuery(this).data('release');    
        vanParams.selectedMedia = jQuery(this).data('media')['cnn-video$id'];
        vanParams.selectedThumb = jQuery(this).data('media')['defaultThumbnailUrl'];
        $pdk.controller.resetPlayer();
        if (vanParams.currentRelease !== "undefined") {
            jQuery('#modal-player-placeholder').hide();        
            $pdk.controller.loadReleaseURL(vanParams.currentRelease,true);
        }
        else {
            jQuery('#modal-player-placeholder').show()        
        }
    });

    //Update background color when hovering over media
    jQuery(document).on('mouseenter', '.media', function () {
        $this = jQuery(this);
        $this.data('bgc', $this.css('background-color'));
        $this.css('background-color', '#f5f5f5');
    });

    //Update background color when hovering off media
    jQuery(document).on('mouseleave', '.media', function () {
        $this = jQuery(this);
        var oldbgc = $this.data('bgc');

        if (oldbgc) $this.css('background-color', oldbgc);
        else $this.css('background-color', '');

    });

    /**
     * Set the page layout 
     */
    var container = window.parent.document.getElementById('tp-container')
    if (container)
        container.style.height = window.parent.innerHeight;

    jQuery('#info-affix').affix({
        offset: {
            top: 0
        }
    });

    jQuery('#filter-affix').affix({
        offset: {
            top: 0
        }
    });

});

function notifyUser(type, msg){
    var $msgPanel = jQuery('#message-panel');
    $msgPanel.attr('class','');
    if (type === 'clear'){
        $msgPanel.attr('class','');
        msg = '';
    }else{
        $msgPanel.addClass('alert alert-' + type);
        $msgPanel.alert();
    }
    $msgPanel.text(msg);
}

/**
 * Refresh the infinite scrolling media list based on the selected category and search options
 * @return {void} 
 */
function refreshView() {
    notifyUser('clear'); //clear alert box.
    var $mediaList = jQuery('#media-list');
    //TODO: If sorting clear search?
    var queryObject = {
        search: jQuery('#input-search').val(),
        category: encodeURI(vanParams.selectedCategory),
        sort: getSort(),            
        dateRange: getDateFilter(),
        desc: jQuery('#sort-desc').data('sort'),
        contentFilter: getContentFilter()       
    };

    vanParams.queryParams = queryObject
    var newFeed = vanHelper.buildMediaQuery("",queryObject);


    vanParams.feedQuery = newFeed;
    displayMessage('');

    vanParams.feedEndRange = 0;
    $mediaList.empty();
    $mediaList.infiniteScroll('reset');
}

function getDateFilter(){
        var filterDateRange = jQuery('option:selected','#date-filter').val();
        var rangeStart = moment();
        var rangeEnd = moment();
        var prevFeedCopy = true;

        switch (filterDateRange.toLowerCase().trim()) {
            case "past hour":
                rangeStart.subtract('minutes',60);
                break;
            case "past 24 hours":
                rangeStart.subtract('hours',24);
                break;
            case "past 48 hours":
                rangeStart.subtract('hours',48);
                break;
            case "past 7 days":
                rangeStart.subtract('days',7);
                break;
            default:
                prevFeedCopy = false;
        }

        if (rangeEnd.unix() > rangeStart.unix())
            return (rangeStart.unix()*1000 +'~'+rangeEnd.unix()*1000);

        return '';
}

function getSort(){
    var sortMethod = jQuery('option:selected','#selectpick-sort').val();
            
    switch (sortMethod.toLowerCase().trim()) {
        case "published":
            sortMethod = "pubDate|desc";
            break;
        case "added":
            sortMethod = "added|desc";
            break;
        case "updated":
            sortMethod = ":cnnUpdatedDate|desc";
            //sortMethod = "updated|desc";
            break;
        case "title":
            sortMethod = "title";
            break;  
    }
    
    return sortMethod || "added";
}

function getSearch(){
    return jQuery('#input-search').val();
}

function getContentFilter(){
    var myContentToggle  = jQuery('input:checkbox','#my-content').prop('checked');
    var rawContentToggle = jQuery('input:checkbox','#raw-content').prop('checked');
    var sourceField = 'source'
    var rawContentField = 'isRawOrFile'
    var filterString = '';

    if (sourceField && myContentToggle && vanParams.provider)
        filterString = '{'+ sourceField +'}{'+ vanParams.provider +'}';

    if (rawContentField && !rawContentToggle)
        filterString += (filterString.length > 0 ? ',' : '') + '{'+ rawContentField +'}{false}';

    return filterString;
}

function buildCategoryAccordion(resp) {
    var entries = resp['entries'];
    for (var idx in entries) {
        var entryTitle = entries[idx]['title'];
        jQuery('#list-categories').append('<a href="#" class="list-group-item cat-list-selector">' + entryTitle + '</a>');
    }

    jQuery('#list-categories').on('mouseover', function () {
        jQuery('body')[0].style.overflowY = 'none';
    });
    jQuery('#list-categories').on('mouseout', function () {
        jQuery('body')[0].style.overflowY = 'auto';
    });

    jQuery('.cat-list-selector', '#list-categories').click(function () {
        vanParams.selectedCategory = jQuery(this).text();
        if (vanParams.selectedCategory == "All Videos") vanParams.selectedCategory = '';
        jQuery('.cat-list-selector', '#list-categories').each(function (idx, item) {
            var $item = jQuery(item);

            if ((vanParams.selectedCategory == $item.text()) || (vanParams.selectedCategory == '' && $item.text() == 'All Videos')) $item.css('background-color', '#D8E8FF');
            else jQuery(item).css('background-color', '');
        });
        jQuery('#input-search').val(''); //Clear the searching when we choose a category        

        refreshView();
    });
}

function addMediaObject(media) {
    //Prevent adding the same media twice.
    // This cannot be filtered out earlier because it only really occurs when
    // Something just gets added.
    if (document.getElementById(media.guid) != null) //Can't use jquery because of poor guid format convention.
    return;
    
    var placeHolder = "";
    if (media.defaultThumbnailUrl === "")
        placeHolder = "holder.js/128x72/text:No Thumbnail";

    var newMedia = '<div class="media" id="' + media.guid + '"><img class="media-object pull-left thumb-img" data-src="' + placeHolder + '" alt="128x72" src="' + media.defaultThumbnailUrl + '">'
    if (location.search.indexOf('&embed=true') != -1)
        newMedia += '<button class="btn btn-xs media-embed pull-right" data-toggle="tooltip" data-placement="bottom" title="Embed this Media"><div class="dashicons dashicons-migrate"></div></button>';
    if (jQuery('#tp-edit-dialog').length !== 0)
        newMedia += '<button class="btn btn-xs media-edit pull-right" data-toggle="tooltip" data-placement="bottom" title="Edit this Media"><div class="dashicons dashicons-edit"></div></button>';
    newMedia += '<div class="media-body">' + '<div id="head"><strong class="media-heading"></strong></div>' + '<div id="source"></div>' + '<div id="desc"></div>' + '</div>' + '</div>';

    newMedia = jQuery(newMedia);

    jQuery('#head > strong', newMedia).text(media.title);    
    if (media.description) {
        if (media.description.length > 300)
            media.description = media.description.substring(0,297) + '...'
        jQuery('#desc', newMedia).text(media.description);
    }    
    
    newMedia.data('guid', media.guid);
    newMedia.data('media', media);
    newMedia.data('id', media.id)

    // media['defaultThumbRelease'] = vanHelper.getDefaultThumbRelease(media.thumbnails);
    
    var previewUrl = vanHelper.extractVideoUrlfromMedia(media);
    if (previewUrl.length > 0)
        newMedia.data('release',previewUrl.pop().appendParams({mbr: true}));
    else
        newMedia.data('release','http://link.theplatform.com/s/van-dev/OB6AWyUuBu8V');//If no valid video, this url is an unavailable message.
    
    jQuery('.media-embed', newMedia).hover(function() {
        jQuery(this).tooltip();
    }, function() {
        jQuery(this).attr('title', 'Embed this Media').tooltip('fixTitle');
    });

    jQuery('.media-edit', newMedia).hover(function() {
        jQuery(this).tooltip();
    }, function() {
        jQuery(this).attr('title', 'Edit this Media').tooltip('fixTitle');
    });

    jQuery('.media-edit', newMedia).click(function() {
        jQuery(newMedia).click();
        vanParams.mediaId = newMedia.data('id');
    
        if (newMedia != '') {
            jQuery("#tp-edit-dialog").dialog({
                    dialogClass: "wp-dialog", 
                    modal: true, 
                    resizable: true, 
                    minWidth: 800, 
                    width: 1024,
                    position: ['center',20]                   
                }).css("overflow","hidden");    
        }

        return false;
    });

    jQuery('#media-list').append(newMedia);

     //Select the first one on the page.
    if (jQuery('#media-list').children().length < 2)
        jQuery('.media','#media-list').click();
}

function updateContentPane(mediaItem) {
    var i, catArray, catList;    
    jQuery('#media-video-id').text(mediaItem['cnn-video$id']  || '');
    jQuery('#media-title').text(mediaItem.title  || '');
    jQuery('#media-description').text(mediaItem.description  || '');

    catArray = mediaItem.categories || [];
    catList = '';
    for (i = 0; i < catArray.length; i++){
        if (catList.length > 0) catList += ', ';
        catList += catArray[i].name;
    }
    jQuery('#media-categories').text(catList);

//TODO: Figure out how to store namespacing?
catArray = mediaItem['cnn-video$additionalCategories'] || [];
catList = '';
for (i = 0; i < catArray.length; i++){
    if (catList.length > 0) catList += ', ';
    catList += catArray[i];
}
jQuery('#media-addl-categories').text(catList);

jQuery('#media-provider').text(mediaItem['cnn-video$source'] || mediaItem['cnn-video$videoSource']);

jQuery('#media-embargoes').text(mediaItem['cnn-video$embargoes'] || '');

jQuery('#media-keywords').text(mediaItem.keywords.split(',').join(', ')  || '');

jQuery('#media-pubdate').text(new Date(mediaItem.pubDate).toLocaleString()  || '');

jQuery('#media-updated').text(new Date(mediaItem.updated).toLocaleString()  || '');

jQuery('#media-expiration').text(new Date(mediaItem.expirationDate).toLocaleString()  || '');

jQuery('#media-thumbnail').text(mediaItem['defaultThumbnailUrl']);
}

function displayMessage(msg) {
    jQuery('#msg').text(msg);
}

