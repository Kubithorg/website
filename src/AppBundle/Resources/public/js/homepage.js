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


(function()
{
	'use strict';
/*
	forEachElement('ul.progress-goals li > span', function(goal_anchor, i)
	{
		goal_anchor.addEventListener('mouseover', function(e)
		{
			var target_detail_box = this.nextElementSibling;

			forEachElement('ul.progress-goals li aside.is-active', function(detail_box, i)
			{
				if (detail_box != target_detail_box)
					detail_box.classList.remove('is-active');
			});

			this.nextElementSibling.classList.toggle('is-active');
		});
	});

	document.addEventListener('click', function (e)
	{
	    if (!getClosest(e.target, 'ul.progress-goals li aside.is-active, ul.progress-goals li > span'))
	    {
	        forEachElement('ul.progress-goals li aside.is-active', function(detail_box, i)
			{
				detail_box.classList.remove('is-active');
			});
	    }
	}, false);
	*/
})();
