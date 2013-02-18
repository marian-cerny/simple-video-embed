<h1>Simple video embed plug-in for Wordpress</h1>

<p>
This plug-in enables you insert HTML5 videos (with flash fallback) with simple custom controls using a shortcode. Works for single videos, or whole directories. All you need to do is specify the video (without the extension) or directory name.
</p>

<p>
The plug-in automatically inserts all videos with the given filename and all existing extensions. If there's an image file with the same name present, it will be automatically used as the poster/thumbnail for the video.
</p>

<p>
When outputting a directory, only the thumbnails are shown. Upon clicking a thumbnails, the video is shown inside a popup box (using fancybox).
</p>

<h2>Shortcodes</h2>

<p>
There are 3 shorcodes available:

<ul>
	<li>[sve filename='myvideo']</li> - simply outputs a single video
	<li>[sve_popup filename='myvideo']</li> - outputs thumbnail for a single video which is displayed in a popup box
	<li>[sve_folder video_directory='myvideo']</li> - outputs all videos (thumbnails) in the specified directory. Directory should be relative to '/wp-content/uploads' (default is '/video/')
</ul>

</p>

<p>
All settings can be overridden with shortcode parameters.
</p>

