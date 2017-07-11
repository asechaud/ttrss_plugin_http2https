# ttrss_plugin_http2https
A plugin to avoid « mixed content » when images are loaded over http and tt-rss is loaded over https.
The images will be downloaded by the server then sent over https.

For the installation :
Put the folder «http2https» with the two files «init.php» and «https.php» into plugins/ of ttrss.
Go to Configuration, then Plugins and activate http2https.
That's all !
