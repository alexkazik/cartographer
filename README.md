7th Continent Cartographer
==========================

A small tool to create maps for the 7th continent.


Requirements
============

- PHP (tested with 7.1)


Demo
====

Please see the demo branch and the released demo on how to add
files and what the final product looks like.

The images have a low resolution to save bandwidth. You can use
images of any size and all of them will be scaled to 640*640
(even if the original is not square).


Usage
=====

- Add images of all cards you want to the `images-c*d*` folder
- Edit *.txt and add the cards
- Run `./gen.sh`
- Open `./web/<area>.html`

Images
------

The images must be of type JPEG and with extension `jpeg`.

I use the c and d number to keep track of in which campaign and
day we saw the card at first. But feel free to add them all into one folder.

Texts
-----

The files can be named as you like, for each one a html files is
generated.

In the file there is a grid to add cards to all of the areas.
The optional leading `!` denotes a golden card, in any case you have to write the
card number followed by a colon and the card id (which must be identical to the filename).

After each run the texts are updated to look more nicely and add new free spaces for you to fill.

To show a fog card simply fill `fog` in.



