# Substitute - the Moodle "Find and Replace" filter

Because there wasn't one that looked for arbitary text and replaced it with arbitary html. You might use this where you have a particular peice of formatting you re-use dozens of times over in your courses or blocks and just want to make your life easier.

It offers 16 slots for text you can find. I could have offered an unlimited number but text filters are applied to all text strings on the page so setting some limits is probably best. YMMV.

You enter the text you want to find. You probably want to use a text string that probably won't come up where you do not expect it - for instance `[[my-pattern-5]]`.

You then enter the html you want to replace it with. There's a handy standard editor for this. Linking to images is a bit of an issue since the standard admin editor doesn't have the repository button. So host your images elsewhere on the site, or on www.imgur.com or similar and just hardlink to them.

Can you add ... ?
-----------------

No. If you're looking for a more complete substitution filter plugin that offers user- or course-aware replacements, built in keywords, language awareness, etc I suggest you look at https://github.com/michael-milette/moodle-filter_filtercodes.

Compatibility
-------------

Built on Moodle 3.8, probably works for 2.x branches too.

Installation
------------

Use the plugin installer (reccomended), or put the 'substitute' folder into your moodle filter folder.

Licence
-------
GPL3