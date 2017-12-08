/*! getClosest.js | (c) 2017 Chris Ferdinandi | MIT License | http://github.com/cferdinandi/getClosest */
/**
 * Get the closest parent element that matches a selector. 
 * @param  {Element} elem     Starting element
 * @param  {String}  selector Selector to match against
 * @return {Boolean|Element}  Returns null if not match found
 */
var getClosest = function (elem, selector)
{
    // Element.matches() polyfill
    if (!Element.prototype.matches)
    {
        Element.prototype.matches =
            Element.prototype.matchesSelector ||
            Element.prototype.mozMatchesSelector ||
            Element.prototype.msMatchesSelector ||
            Element.prototype.oMatchesSelector ||
            Element.prototype.webkitMatchesSelector ||
            function(s)
            {
                var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                    i = matches.length;
                while (--i >= 0 && matches.item(i) !== this) {}
                return i > -1;
            };
    }

    // Get closest match
    for (; elem && elem !== document; elem = elem.parentNode)
    {
        if (elem.matches(selector)) return elem;
    }

    return null;
};

function forEachElement(selector, fn)
{
  var elements = document.querySelectorAll(selector);
  for (var i = 0; i < elements.length; i++)
    fn(elements[i], i);
}

