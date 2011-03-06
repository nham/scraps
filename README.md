Scraps - web-based note-taking app
==================================

What is it? Tell me. Tell me now.
--------------------------------
The idea behind Scraps is a web app for saving pieces of text ("scraps"). Content can be formatted via Markdown, so a user can enter plain text, URLs, code blocks, essays, or anything else suitable for format by Markdown. The philosophy behind scraps is to act as a brain dump for everything you might want to save or lookup later or perhaps just want to get out of your head.

This is unimplemented as of yet, but it will eventually be possible to search content scraps. There is no plan as of yet to implement anything like tags or content types, so the search will just be a plain text search returning any scrap that contains the search text. (I'll probably make it an instant search for maximum fanciness.)


Show me ya guts
---------------
Scraps utilizes (among other libraries) Backbone.js. It's a single page app which interfaces with a REST-like PHP backend using a SQLite database. 


How to do
------------
 - **Create scrap:** Click the "new scrap" link at the top of the page to bring up a textarea for typing in notes.
 
 - **Edit scrap:** Double click anywhere on a scrap to bring up a textarea containing the source text of the scrap.

 - **Delete scrap:** Click the X in the upper-right of the scrap and click OK in the dialog that pops up to confirm deletion.

