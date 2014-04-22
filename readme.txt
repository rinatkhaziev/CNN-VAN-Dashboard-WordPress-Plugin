=== CNN VAN Affliate Dashboard ===
Developed By: thePlatform for Media, Inc.
Tags: embedding, video, embed, portal, theplatform, shortcode
Requires at least: 3.7
Tested up to: 3.9
Stable tag: 1.0.0

Manage your content hosted by thePlatform and embed media in WordPress posts.

== Description ==
View your content hosted by thePlatform for Media and easily embed videos from your library in WordPress posts, modify media metadata, and upload new media.

== Installation ==
Copy the folder "cnn-van-dashboard" with all included files into the "wp-content/plugins" folder of WordPress. Activate the plugin and set your feed id and affiliate name in the plugin settings.

== Screenshots ==

== Changelog ==

= 1.0.0 =
Initial release

== Configuration ==

= CNN VAN Affiliate Options =
CNN VAN Feed PID - Your MPX Feed Public ID	
CNN VAN Affiliate ID- Your CNN VAN Affiliate identifier
Default Video Width	- The default Video embed width
Auto Start Videos - Whether or not to auto start videos

= Filters =
van_embed_code - The CNN Embed code
van_feed_url - The Base VAN Feed URL

= Capabilities filters = 
van_embedder_cap (default - edit_posts) - Embed Videos in posts
van_admin_cap  (default - manage_options) - Configure the plugin

= Shortcode parameters =
id - CNN VAN Video ID
width - Override the default width
autostart - true/false, override the default autostart setting