<?php

namespace Oro\Bundle\Html5PurifierBundle\Tests\Functional\Provider;

use Oro\Bundle\Html5PurifierBundle\Provider\Html5TagProvider;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class Html5TagProviderTest extends WebTestCase
{
    /** @var Html5TagProvider */
    protected $html5TagProvider;

    /**
     * List of allowed element.
     *
     * @url http://www.tinymce.com/wiki.php/Configuration:valid_elements
     * @var array
     */
    protected $allowedElements = [
        '@[style|class]',
        'table[cellspacing|cellpadding|border|align|width]',
        'thead[align|valign]',
        'tbody[align|valign]',
        'tr[align|valign]',
        'td[align|valign|rowspan|colspan|bgcolor|nowrap|width|height]',
        'a[!href|target|title]',
        'dl',
        'dt',
        'div[id]',
        'ul',
        'ol',
        'li',
        'em',
        'strong',
        'b',
        'p',
        'font[color]',
        'i',
        'br',
        'span[id]',
        'img[src|width|height|alt]',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hgroup',
        'abbr',
        'address',
        'article',
        'audio',
        'bdo',
        'blockquote',
        'caption',
        'cite',
        'code',
        'col',
        'colgroup',
        'dd',
        'del',
        'details',
        'dfn',
        'figure',
        'footer',
        'header',
        'hr',
        'ins',
        'kbd',
        'mark',
        'menu',
        'nav',
        'pre',
        'q',
        'samp',
        'section',
        'small',
        'source',
        'sub',
        'sup',
        'time',
        'tfoot',
        'var',
        'video',
        'aside',
    ];

    /** @var string */
    protected $allowedTags = '';

    protected function setUp()
    {
        $this->initClient();
        $this->html5TagProvider = $this->getContainer()->get('oro_form.provider.html_tag_provider');

        $this->allowedTags = '<table></table><thead></thead><tbody></tbody><tr></tr><td></td><a></a><dl></dl>' .
            '<dt></dt><div></div><ul></ul><ol></ol><li></li><em></em><strong></strong><b></b><p></p><font></font>' .
            '<i></i><br><span></span><img><h1></h1><h2></h2><h3></h3><h4></h4><h5></h5><h6></h6><hgroup></hgroup>' .
            '<abbr></abbr><address></address><article></article><audio></audio><bdo></bdo><blockquote></blockquote>' .
            '<caption></caption><cite></cite><code></code><col></col><colgroup></colgroup><dd></dd><del></del>' .
            '<details></details><dfn></dfn><figure></figure><footer></footer><header></header><hr></hr>' .
            '<ins></ins><kbd></kbd><mark></mark><menu></menu><nav></nav><pre></pre><q></q><samp></samp>' .
            '<section></section><small></small><source></source><sub></sub><sup></sup><time></time><tfoot></tfoot>' .
            '<var></var><video></video><aside></aside>';
    }

    public function testGetAllowedElements()
    {
        $this->assertEquals($this->allowedElements, $this->html5TagProvider->getAllowedElements());
    }

    public function testGetAllowedTags()
    {
        $this->assertEquals($this->allowedTags, $this->html5TagProvider->getAllowedTags());
    }
}
