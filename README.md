Html5PurifierBundle
===================

The purpose of this bundle is to add `xemlock/htmlpurifier-html5` library support without introducing the BC break.
The library adds support for HTML5 tags to HTMLPurifier.
The bundle also adds the ability to switch between three purification modes:
- `strict` - filter html elements and attributes by white list. Style and iframe elements are not allowed.
- `extended` - same as strict but style and iframe elements are allowed.
- `disabled` - HTML Purifier is disabled completely.

The modes are switched with `html_purifier_mode` config setting in [package/html5-purifier/Resources/config/oro/app.yml](package/html5-purifier/Resources/config/oro/app.yml)

### How to Extend the List of Supported Elements and Attributes in Html5Purifier

Sometimes it may be necessary to add additional elements or attributes to the Html5Purifier config.
To add a custom element, decorate  the `oro_html5_purifier.html_tag_helper` service.

```yaml
services:
    ...
    acme.html_tag_helper:
        decorates: oro_html5_purifier.html_tag_helper
        parent: oro_html5_purifier.html_tag_helper
        calls:
            # map element
            - ['setElement', ['map', 'Block', 'Flow', 'Common', true]]
            # map attributes
            - ['setAttribute', ['map', 'id', 'ID']]
            - ['setAttribute', ['map', 'name', 'CDATA']]
```

Next, add the `map` element and attributes into the purifier config in the `app.yml` file in your bundle.

```yaml
oro_form:
    wysiwyg:
        html_allowed_elements:
            map:
                attributes:
                    - id
                    - name
```
Now you can use the `map` tag and its `id` and `name` attributes in all WYSIWYG fields.
