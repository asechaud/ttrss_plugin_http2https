# ttrss_plugin_http2https
A plugin to avoid « mixed content » when images are loaded over http and tt-rss is loaded over https.
The images will be downloaded by the server then sent over https.

The default plugin af_zz_imgproxy do the same job, so this one isn't needed.


For the installation, if you want to try it anymore :
Put the folder «http2https» with the two files «init.php» and «https.php» into plugins.local/ of ttrss.
Go to Configuration, then Plugins and activate http2https.
That's all !
