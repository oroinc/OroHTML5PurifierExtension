Html5PurifierBundle
===================

The purpose of this bundle is to add `xemlock/htmlpurifier-html5` library support without introducing the BC break.
The library adds support for HTML5 tags to HTMLPurifier.
The bundle also adds the ability to switch between three purification modes:
- `strict` - filter html elements and attributes by white list. Style and iframe elements are not allowed.
- `extended` - same as strict but style and iframe elements are allowed.
- `disabled` - HTML Purifier is disabled completely.

The modes are switched with `html_purifier_mode` config setting in [package/html5-purifier/Resources/config/oro/app.yml](package/html5-purifier/Resources/config/oro/app.yml)
