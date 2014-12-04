Themes
======

How to create a theme for Miniflux?
-----------------------------------

It's very easy to write a custom theme for Miniflux.

A theme is just a CSS file, images and fonts.
A theme doesn't change the behaviour of the application but only the page layout.

The first step is to create a new directory structure for your theme:

```bash
mkdir -p themes/mysuperskin/{css,img,fonts}
```

The name of your theme should be only alphanumeric.

There is the following directories inside your theme:

- `css`: Your stylesheet, the file must be named `app.css` (required)
- `img`: Theme images (not required)
- `fonts`: Theme fonts (not required)

Miniflux use responsive design, so it's better if your theme can handle mobile devices as well.

If you write a very cool theme for Miniflux, **send me your theme to be available in the default installation!**
It would be awesome for everybody :)

List of themes
--------------

Original theme by Frederic Guillot

Other themes included in the default installation:

- Bootstrap Light by Silvus
- Bootswatch Cyborg by Silvus
- Cards by Augustin Lacour
- Copper by Nicolas Dewaele
- Green by Maxime (aka EpocDotFr)
- Hello by Mirado (https://github.com/meradoou)
- Midnight by Luca Marra
- NoStyle by Frederic Guillot
- Still by Franklin

**PS: Those themes are maintained and tested by their respective authors.**
